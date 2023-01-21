<?php
/**
 * Created by PhpStorm.
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.7.0
 */

use \Bitrix\Main\Localization\Loc;

$tabControl->BeginNextTab();
?>
    <tr>
        <td colspan="2" align="left">
            <div class="o-container--super">
                <div class="o-grid">
                    <div class="o-grid__cell o-grid__cell--width-70">
                        <div class="c-card">
                            <div class="c-card__body">
                                <p class="c-paragraph"><?= Loc::getMessage('LABEL_TITLE_HELP_BEGIN') ?>.</p>
                                <?= Loc::getMessage('LABEL_TITLE_HELP_BEGIN_TEXT'); ?>
                            </div>
                        </div>
                        <div class="o-container--large">
                            <h2 id="yaPay"
                                class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_TEXT'); ?></h2>
                            <iframe src="https://yoomoney.ru/quickpay/shop-widget?writer=seller&default-sum=1000&button-text=11&payment-type-choice=on&mobile-payment-type-choice=on&successURL=&quickpay=shop&account=410011413398643&targets=%D0%9F%D0%B5%D1%80%D0%B5%D0%B2%D0%BE%D0%B4%20%D0%BF%D0%BE%20%D0%BA%D0%BD%D0%BE%D0%BF%D0%BA%D0%B5&" width="100%" height="222" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
                            <h2 id="morePay"
                                class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_ALL_TEXT'); ?></h2>
                            <table class="c-table">
                                <tbody class="c-table__body c-table--striped">
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Tinkoff Bank and Bank Card</td>
                                    <td class="c-table__cell">
                                        <a href="https://www.tinkoff.ru/cf/36wVfnMf7mo" target="_blank">
                                            https://www.tinkoff.ru/cf/36wVfnMf7mo
                                        </a>
                                    </td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Yandex.Money</td>
                                    <td class="c-table__cell">
                                        <a href="https://yoomoney.ru/to/410011413398643">410011413398643</a>
                                    </td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMR (rub)</td>
                                    <td class="c-table__cell">R218843696478</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMU (uah)</td>
                                    <td class="c-table__cell">U135571355496</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMZ (usd)</td>
                                    <td class="c-table__cell">Z418373807413</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WME (euro)</td>
                                    <td class="c-table__cell">E331660539346</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMX (btc)</td>
                                    <td class="c-table__cell">X740165207511</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WML (ltc)</td>
                                    <td class="c-table__cell">L718094223715</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Webmoney WMH (bch)</td>
                                    <td class="c-table__cell">H526457512792</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">PayPal</td>
                                    <td class="c-table__cell">
                                        <a href="https://www.paypal.me/darkfriend" target="_blank">paypal.me/@darkfriend</a>
                                    </td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Payeer</td>
                                    <td class="c-table__cell">P93175651</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Bitcoin</td>
                                    <td class="c-table__cell">15Veahdvoqg3AFx3FvvKL4KEfZb6xZiM6n</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Litecoin</td>
                                    <td class="c-table__cell">LRN5cssgwrGWMnQruumfV2V7wySoRu7A5t</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">Ethereum</td>
                                    <td class="c-table__cell">0xe287Ac7150a087e582ab223532928a89c7A7E7B2</td>
                                </tr>
                                <tr class="c-table__row">
                                    <td class="c-table__cell">BitcoinCash</td>
                                    <td class="c-table__cell">bitcoincash:qrl8p6jxgpkeupmvyukg6mnkeafs9fl5dszft9fw9w
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <h2 id="moreThanks"
                                class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_OTHER_TEXT'); ?></h2>
                            <?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_OTHER_TEXT_S'); ?>
                        </div>
                    </div>
                    <div class="o-grid__cell o-grid__cell--width-30">
                        <h2 id="moreThanks"
                            class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_FOLLOW'); ?></h2>
                        <table class="c-table">
                            <tbody class="c-table__body">
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://vk.com/dev2fun" target="_blank">vk.com/dev2fun</a>
                                </td>
                            </tr>
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://facebook.com/dev2fun" target="_blank">facebook.com/dev2fun</a>
                                </td>
                            </tr>
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://twitter.com/dev2fun" target="_blank">twitter.com/dev2fun</a>
                                </td>
                            </tr>
                            <tr class="c-table__row">
                                <td class="c-table__cell">
                                    <a href="https://t.me/dev2fun" target="_blank">telegram/dev2fun</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </td>
    </tr>