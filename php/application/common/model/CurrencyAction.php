<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 17:52
 */

namespace app\common\model;
/**
 * Class CurrencyAction
 * @package V4\Model
 *
 *
 * @internal 第三方：指理财平台
 */
class CurrencyAction
{
    /********************* 现金余额 cash  ************************ */
    /**
     * 现金余额充值
     */
    const CashRecharge = 801;

    /**
     * 佣金结算到现金
     */
    const CashFromCommissionClean = 802;

    /**
     * 佣金累计结算到现金
     */
    const CashFromCommissionCleanAll = 803;

    /**
     * 提现失败退回
     */
    const CashWithdrawFaillBack = 804;

    /**
     * 微信消费充值
     */
    const CashRechargeWechatResume = 805;

    /**
     * 支付宝消费充值
     */
    const CashRechargeAlipayResume = 806;





    /**
     * 提现到支付宝
     */
    const CashWithdrawToAlipay = 850;

    /**
     * 提现到微信
     */
    const CashWithdrawToWechat = 851;

    /**
     * 提现到银行卡
     */
    const CashWithdrawToBank = 852;

    /**
     * 扣除个人所得税
     */
    const CashDeductionGeren = 853;

    /**
     * 扣除平台管理费
     */
    const CashDeductionPingtai = 854;

    /**
     * 扣除技术服务费
     */
    const CashDeductionJishu = 855;

    /**
     * 扣除提现手续费
     */
    const CashTixianFee = 856;

    /**
     * 支付宝消费
     */
    const CashAlipayResume = 857;

    /**
     * 微信消费
     */
    const CashWechatResume = 858;


    /********************* 预估佣金 commission  ************************ */
    
    /**
     * 一级佣金
     */
    const CommissionFirst = 601;

    /**
     * 上级佣金
     */
    const CommissionSecond = 602;

    /**
     * 运营佣金
     */
    const CommissionOperations = 603;

    /**
     * 运营奖金[直属下级 运营达人佣金的x%]
     */
    const CommissionOperationsChilds = 604;

    /**
     * 佣金解冻
     */
    const CommissionFreezeReturn = 605;

    /**
     * 玩主奖金
     */
    const CommissionPlayerhostChild = 606;

    /**
     * 新人免单全返
     */
    const CommissionReturnAll = 607;


    /**
     * 佣金结算现金[每次0点自动结算:前一天的一级佣金、上级佣金、运营佣金]
     */
    const CommissionAutoCleanToCash = 651;

    /**
     * 佣金结算现金[次月结算：运营奖金8%、玩主奖金2%]
     */
    const CommissionAdminCleanToCash = 652;


    /**
     * 佣金冻结
     */
    const CommissionFreeze = 653;

    /********************* 积分 points  ************************ */


    /**
     * 获取货币类型名称
     * @param CurrencyAction $currencyAction
     * @return string
     */
    public static function getLabel($currencyAction)
    {
        switch ($currencyAction) {
            //现金币
            case CurrencyAction::CashRecharge:
                return '现金余额充值';
            case CurrencyAction::CashFromCommissionClean:
                return '佣金结算到现金';
            case CurrencyAction::CashWithdrawFaillBack:
                return '提现失败退回';
            case CurrencyAction::CashWithdrawToAlipay:
                return '提现到支付宝';
            case CurrencyAction::CashWithdrawToWechat:
                return '提现到微信';
            case CurrencyAction::CashWithdrawToBank:
                return '提现到银行卡';

            case CurrencyAction::CommissionFirst:
                return '一级佣金';
            case CurrencyAction::CommissionSecond:
                return '上级佣金';
            case CurrencyAction::CommissionOperations:
                return '运营佣金';
            case CurrencyAction::CommissionOperationsChilds:
                return '运营奖金';
            case CurrencyAction::CommissionPlayerhostChild:
                return '玩主奖金';
            case CurrencyAction::CommissionAutoCleanToCash:
                return '佣金自动结算现金';
            case CurrencyAction::CommissionAdminCleanToCash:
                return '佣金后台结算现金';
            case CurrencyAction::CommissionFreezeReturn:
                return '佣金解冻';
            case CurrencyAction::CommissionFreeze:
                return '佣金冻结';



            default:
                return $currencyAction . '未知';
        }
    }




}
