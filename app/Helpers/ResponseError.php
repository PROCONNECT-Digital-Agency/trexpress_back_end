<?php

namespace App\Helpers;

class ResponseError
{
    public const NO_ERROR = 'NO_ERROR'; // 'OK'
    public const ERROR_100 = 'ERROR_100'; // 'User is not logged in.'
    public const ERROR_101 = 'ERROR_101'; // 'User does not have the right roles.'
    public const ERROR_102 = 'ERROR_102'; // 'Login or password is incorrect.'
    public const ERROR_103 = 'ERROR_103'; // 'User email address is not verified'
    public const ERROR_104 = 'ERROR_104'; // 'User phone number is not verified'
    public const ERROR_105 = 'ERROR_105'; // 'User account is not verified'
    public const ERROR_106 = 'ERROR_106'; // 'User already exists'
    public const ERROR_107 = 'ERROR_107'; // 'Please login using facebook or google'
    public const ERROR_108 = 'ERROR_108'; // 'User doesn't have Wallet'
    public const ERROR_109 = 'ERROR_109'; // 'Insufficient wallet balance'
    public const ERROR_110 = 'ERROR_110'; // 'Can't update this user role'
    public const ERROR_111 = 'ERROR_111'; // 'Can't add children category to this category'
    public const ERROR_114 = 'ERROR_114'; // 'Seller doesn't have Wallet'

    public const ERROR_200 = 'ERROR_200'; // 'Success'
    public const ERROR_201 = 'ERROR_201'; // 'Wrong OTP Code'
    public const ERROR_202 = 'ERROR_202'; // 'Too many request, try later'
    public const ERROR_203 = 'ERROR_203'; // 'OTP code is expired'

    public const ERROR_204 = 'ERROR_204'; // 'You are not seller yet or your shop is not approved'
    public const ERROR_205 = 'ERROR_205'; // 'Shop already created'
    public const ERROR_206 = 'ERROR_206'; // 'User already has Shop'
    public const ERROR_207 = 'ERROR_207'; // 'Can't update Shop Seller'
    public const ERROR_208 = 'ERROR_208'; // 'Subscription already active'

    public const ERROR_249 = 'ERROR_249'; // 'Invalid coupon'
    public const ERROR_250 = 'ERROR_250'; // 'Coupon expired'
    public const ERROR_251 = 'ERROR_251'; // 'Coupon already used'
    public const ERROR_252 = 'ERROR_252'; // 'Status already used'
    public const ERROR_253 = 'ERROR_253'; // 'Wrong status type'
    public const ERROR_254 = 'ERROR_254'; // 'Can't update Cancel status'
    public const ERROR_210 = 'ERROR_210'; // 'Delivery already attached'
    public const ERROR_211 = 'ERROR_211'; // 'invalid deliveryman or token not found'
    public const ERROR_212 = 'ERROR_212'; // 'Not your shop. Check your other account'
    public const ERROR_213 = 'ERROR_213'; // 'Your order amount is full'
    public const ERROR_214 = 'ERROR_214'; // 'Both order has position'
    public const ERROR_215 = 'ERROR_215'; // 'This position attached other order'

    public const ERROR_302 = 'ERROR_302'; // 'Your order amount is full'

    public const ERROR_400 = 'ERROR_400'; // 'Bad request'
    public const ERROR_403 = 'ERROR_403'; // 'Your project is not activated'
    public const ERROR_404 = 'ERROR_404'; // 'Item\'s not found.'
    public const ERROR_413 = 'ERROR_413'; // 'Undefined Type'
    public const ERROR_415 = 'ERROR_415'; // 'No connection to database'
    public const ERROR_429 = 'ERROR_429'; // 'Too many requests'
    public const ERROR_430 = 'ERROR_430'; // 'Stock quantity 0'
    public const ERROR_431 = 'ERROR_431'; // 'Please check product type on category'
    public const ERROR_432 = 'ERROR_432'; // 'You can review after order status delivered'
    public const ERROR_433 = 'ERROR_433'; // 'You can't delete address'
    public const ERROR_434 = 'ERROR_434'; // 'You can't delete record. Seller has a shop'
    public const ERROR_435 = 'ERROR_435'; // 'You can't delete record. Deliveryman has order'

    public const ERROR_500 = 'ERROR_500'; // 'Internal server error.'
    public const ERROR_501 = 'ERROR_501'; // 'Error during created.'
    public const ERROR_502 = 'ERROR_502'; // 'Error during updated.'
    public const ERROR_503 = 'ERROR_503'; // 'Error during deleting.'
    public const ERROR_504 = 'ERROR_504'; // 'Can't delete record that has children.'
    public const ERROR_505 = 'ERROR_505'; // 'Can't delete default record.'
    public const ERROR_506 = 'ERROR_506'; // 'Already exists.'
    public const ERROR_507 = 'ERROR_507'; // 'Can't delete record that has products.'
    public const ERROR_508 = 'ERROR_508'; // 'Excel format incorrect or data invalid.'
    public const ERROR_509 = 'ERROR_509'; // 'Excel format incorrect or data invalid.'
    public const ERROR_511 = 'ERROR_511'; // 'Can't delete record that has relation other records.'



    public const NEW_ORDER                          = 'NEW_ORDER';
    public const CONFIRMATION_CODE                  = 'CONFIRMATION_CODE';
    public const PHONE_OR_EMAIL_NOT_FOUND           = 'PHONE_OR_EMAIL_NOT_FOUND';
    public const ORDER_NOT_FOUND                    = 'ORDER_NOT_FOUND';
    public const ORDER_REFUNDED                     = 'ORDER_REFUNDED';
    public const ORDER_PICKUP                       = 'ORDER_PICKUP';
    public const SHOP_NOT_FOUND                     = 'SHOP_NOT_FOUND';
    public const OTHER_SHOP                         = 'OTHER_SHOP';
    public const SHOP_OR_DELIVERY_ZONE              = 'SHOP_OR_DELIVERY_ZONE';
    public const NOT_IN_POLYGON                     = 'NOT_IN_POLYGON';
    public const NOT_IN_PARCEL_POLYGON              = 'NOT_IN_PARCEL_POLYGON';
    public const CURRENCY_NOT_FOUND                 = 'CURRENCY_NOT_FOUND';
    public const LANGUAGE_NOT_FOUND                 = 'LANGUAGE_NOT_FOUND';
    public const CANT_DELETE_ORDERS                 = 'CANT_DELETE_ORDERS';
    public const CANT_UPDATE_ORDERS                 = 'CANT_UPDATE_ORDERS';
    public const STATUS_CHANGED                     = 'STATUS_CHANGED';
    public const SHOP_APPROVED                      = 'SHOP_APPROVED';
    public const ADD_CASHBACK                       = 'ADD_CASHBACK';
    public const BOOKING_STATUS_CHANGED             = 'BOOKING_STATUS_CHANGED';
    public const PAYOUT_ACCEPTED                    = 'PAYOUT_ACCEPTED';
    public const CANT_DELETE_IDS                    = 'CANT_DELETE_IDS';
    public const USER_NOT_FOUND                     = 'USER_NOT_FOUND';
    public const USER_IS_BANNED                     = 'USER_IS_BANNED';
    public const INCORRECT_LOGIN_PROVIDER           = 'INCORRECT_LOGIN_PROVIDER';
    public const FIN_FO                             = 'FIN_FO';
    public const USER_SUCCESSFULLY_REGISTERED       = 'USER_SUCCESSFULLY_REGISTERED';
    public const USER_CARTS_IS_EMPTY                = 'USER_CARTS_IS_EMPTY';
    public const PRODUCTS_IS_EMPTY                  = 'PRODUCTS_IS_EMPTY';
    public const RECORD_WAS_SUCCESSFULLY_CREATED    = 'RECORD_WAS_SUCCESSFULLY_CREATED';
    public const RECORD_WAS_SUCCESSFULLY_UPDATED    = 'RECORD_WAS_SUCCESSFULLY_UPDATED';
    public const RECORD_WAS_SUCCESSFULLY_DELETED    = 'RECORD_WAS_SUCCESSFULLY_DELETED';
    public const IMAGE_SUCCESSFULLY_UPLOADED        = 'IMAGE_SUCCESSFULLY_UPLOADED';
    public const EMPTY_STATUS                       = 'EMPTY_STATUS';
    public const SUCCESS                            = 'SUCCESS';
    public const DELIVERYMAN_IS_NOT_CHANGED         = 'DELIVERYMAN_IS_NOT_CHANGED';
    public const CATEGORY_IS_PARENT                 = 'CATEGORY_IS_PARENT';
    public const ATTACH_FOR_ADDON                   = 'ATTACH_FOR_ADDON';
    public const TYPE_PRICE_USER                    = 'TYPE_PRICE_USER';
    public const NOTHING_TO_UPDATE                  = 'NOTHING_TO_UPDATE';
    public const WAITER_NOT_EMPTY                   = 'WAITER_NOT_EMPTY';
    public const COOKER_NOT_EMPTY                   = 'COOKER_NOT_EMPTY';
    public const EMPTY                              = 'EMPTY';
    public const ORDER_OR_DELIVERYMAN_IS_EMPTY      = 'ORDER_OR_DELIVERYMAN_IS_EMPTY';
    public const TABLE_BOOKING_EXISTS               = 'TABLE_BOOKING_EXISTS';
    public const DELIVERYMAN_SETTING_EMPTY          = 'DELIVERYMAN_SETTING_EMPTY';
    public const NEW_BOOKING                        = 'NEW_BOOKING';
}
