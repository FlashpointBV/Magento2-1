<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * It is available through the world-wide-web at this URL:
 * https://tldrlegal.com/license/mit-license
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@buckaroo.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@buckaroo.nl for more information.
 *
 * @copyright Copyright (c) Buckaroo B.V.
 * @license   https://tldrlegal.com/license/mit-license
 */

namespace Buckaroo\Magento2\Model\Total\Creditmemo;
use Magento\Framework\App\RequestInterface;

class BuckarooFee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        parent::__construct();
    }

    /**
     * Collect totals for credit memo
     *
     * @param  \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $invoice = $creditmemo->getInvoice();

        $method = $order->getPayment()->getMethod();
        $refundCollection = $order->getCreditmemosCollection();

        $salesModel = ($invoice ? $invoice : $order);

        $refundItem = null;

        $refundItem = $this->request->getPost('creditmemo');

        if ($salesModel->getBaseBuckarooFee()
            && $order->getBaseBuckarooFeeInvoiced() > $order->getBaseBuckarooFeeRefunded()
        ) {
            $baseBuckarooFee = !isset($refundItem['buckaroo_fee_refundable']) && !empty($refundItem) ? 0 : $salesModel->getBaseBuckarooFee();
            $buckarooFee = !isset($refundItem['buckaroo_fee_refundable']) && !empty($refundItem) ? 0 : $salesModel->getBuckarooFee();

            if (preg_match('/afterpay/', $method) && count($refundCollection) > 1) {
                $baseBuckarooFee = 0;
                $buckarooFee = 0;
            }

            $order->setBaseBuckarooFeeRefunded($order->getBaseBuckarooFeeRefunded() + $baseBuckarooFee);
            $order->setBuckarooFeeRefunded($order->getBuckarooFeeRefunded() + $buckarooFee);

            $creditmemo->setBaseBuckarooFee($baseBuckarooFee);
            $creditmemo->setBuckarooFee($buckarooFee);
        }

        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $creditmemo->setBaseGrandTotal(
            $creditmemo->getBaseGrandTotal() +
            $creditmemo->getBaseBuckarooFee()
        );
        /**
         * @noinspection PhpUndefinedMethodInspection
         */
        $creditmemo->setGrandTotal(
            $creditmemo->getGrandTotal() +
            $creditmemo->getBuckarooFee()
        );

        return $this;
    }
}
