<?
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.2.5
 */

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use \Dev2fun\ImageCompress\Check;

if (!$USER->isAdmin()) {
	$APPLICATION->authForm('Nope');
}
$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$curModuleName = "dev2fun.imagecompress";
Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule(\Dev2funImageCompress::MODULE_ID);

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("MAIN_TAB_SET"),
		"ICON" => "main_settings",
		"TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET")
	),
	array(
		"DIV" => "donate",
		"TAB" => Loc::getMessage('SEC_DONATE_TAB'),
		"ICON" => "main_user_edit",
		"TITLE" => Loc::getMessage('SEC_DONATE_TAB_TITLE'),
	),
	//    array(
	//        "DIV" => "edit2",
	//        "TAB" => Loc::getMessage("MAIN_TAB_6"),
	//        "ICON" => "main_settings",
	//        "TITLE" => Loc::getMessage("MAIN_OPTION_REG")
	//    ),
	//    array(
	//        "DIV" => "edit3",
	//        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
	//        "ICON" => "main_settings",
	//        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
	//    ),
	//    array("DIV" => "edit8", "TAB" => GetMessage("MAIN_TAB_8"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_EVENT_LOG")),
	//    array("DIV" => "edit5", "TAB" => GetMessage("MAIN_TAB_5"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_OPTION_UPD")),
	//    array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);

//$tabControl = new CAdminTabControl("tabControl", array(
//    array(
//        "DIV" => "edit1",
//        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
//        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
//    ),
//));

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($request->isPost() && check_bitrix_sessid()) {

	if ($request->getPost('test_module')) {
		$text = array();
		$error = false;
		$algorithmJpeg = Option::get($curModuleName, 'opti_algorithm_jpeg');
		$algorithmPng = Option::get($curModuleName, 'opti_algorithm_png');
		if (!Check::isJPEGOptim($algorithmJpeg)) {
			$text[] = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', array('#MODULE#' => 'jpegoptim'));
		}
		if (!Check::isPNGOptim($algorithmPng)) {
			$text[] = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_CHECK_NOFOUND', array('#MODULE#' => 'optipng'));
		}
		if (!$text) {
			$text = Loc::getMessage("D2F_COMPRESS_OPTIONS_TESTED");
		} else {
			$error = true;
			$text = implode(
				PHP_EOL,
				array_merge(
					array(Loc::getMessage("D2F_COMPRESS_OPTIONS_NO_TESTED")),
					$text
				)
			);
		}
		CAdminMessage::showMessage(array(
			"MESSAGE" => $text,
			"TYPE" => (!$error ? 'OK' : 'ERROR'),
		));
	} else {
		try {
			$success = false;
			if ($algorithmJpeg = $request->getPost('opti_algorithm_jpeg')) {
				//			    if(!Check::isJPEGOptim($algorithmJpeg)) {
				//			        if(!$error = Check::$lastError){
				//						$error = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NOT_FOUND_ALGORITHM',array('#MODULE#'=>'jpeg'));
				//                    }
				//                    throw new Exception($error);
				//                }
				Option::set($curModuleName, 'opti_algorithm_jpeg', $algorithmJpeg);
			} else {
				throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', array('#MODULE#' => 'jpeg')));
			}
			if ($algorithmPng = $request->getPost('opti_algorithm_png')) {
				//				if(!Check::isPNGOptim($algorithmPng)) {
				//					if(!$error = Check::$lastError) {
				//						$error = Loc::getMessage('D2F_IMAGECOMPRESS_ERROR_NOT_FOUND_ALGORITHM',array('#MODULE#'=>'png'));
				//					}
				//					throw new Exception($error);
				//				}
				Option::set($curModuleName, 'opti_algorithm_png', $algorithmPng);
			} else {
				throw new Exception(Loc::getMessage('D2F_IMAGECOMPRESS_ALGORITHM_NOT_CHOICE', array('#MODULE#' => 'png')));
			}

			if ($pthJpeg = $request->getPost('path_to_jpegoptim')) {
				$pthJpeg = rtrim($pthJpeg, '/');
				Option::set($curModuleName, 'path_to_jpegoptim', $pthJpeg);
			}

			if ($pthPng = $request->getPost('path_to_optipng')) {
				$pthPng = rtrim($pthPng, '/');
				Option::set($curModuleName, 'path_to_optipng', $pthPng);
			}

			$cntStep = $request->getPost('cnt_step');
			if (!$cntStep) $cntStep = 30;
			Option::set($curModuleName, 'cnt_step', $cntStep);

			$chmod = $request->getPost('change_chmod');
			if (!isset($chmod)) $chmod = '0777';
			Option::set($curModuleName, 'change_chmod', $chmod);

			$enableElement = $request->getPost('enable_element');
			Option::set($curModuleName, 'enable_element', ($enableElement ? 'Y' : 'N'));

			$enableSection = $request->getPost('enable_section');
			Option::set($curModuleName, 'enable_section', ($enableSection ? 'Y' : 'N'));

			$enableResize = $request->getPost('enable_resize');
			Option::set($curModuleName, 'enable_resize', ($enableResize ? 'Y' : 'N'));

			$enableSave = $request->getPost('enable_save');
			Option::set($curModuleName, 'enable_save', ($enableSave ? 'Y' : 'N'));

			Option::set($curModuleName, 'jpegoptim_compress', $request->getPost('jpegoptim_compress'));
			Option::set($curModuleName, 'optipng_compress', $request->getPost('optipng_compress'));

			$jpegCompress = $request->getPost('jpeg_progressive');
			Option::set($curModuleName, 'jpeg_progressive', ($jpegCompress ? 'Y' : 'N'));

			$resizeImageEnable = $request->getPost('resize_image_enable');
			Option::set($curModuleName, 'resize_image_enable', ($resizeImageEnable ? 'Y' : 'N'));
			if ($resizeImageEnable) {
				$resizeImageWidth = $request->getPost('resize_image_width');
				if (!$resizeImageWidth) $resizeImageWidth = 1280;
				Option::set($curModuleName, 'resize_image_width', $resizeImageWidth);

				$resizeImageHeight = $request->getPost('resize_image_height');
				if (!$resizeImageHeight) $resizeImageHeight = 99999;
				Option::set($curModuleName, 'resize_image_height', $resizeImageHeight);

				$resizeImageAlgorithm = $request->getPost('resize_image_algorithm');
				if (!$resizeImageAlgorithm) $resizeImageAlgorithm = 0;
				Option::set($curModuleName, 'resize_image_algorithm', $resizeImageAlgorithm);
			} else {
				Option::set($curModuleName, 'resize_image_width', '');
				Option::set($curModuleName, 'resize_image_height', '');
				Option::set($curModuleName, 'resize_image_algorithm', 0);
			}

			$msg = Loc::getMessage("D2F_COMPRESS_REFERENCES_OPTIONS_SAVED");
			$success = true;
		} catch (Exception $e) {
			$msg = $e->getMessage();
		}

		CAdminMessage::showMessage(array(
			"MESSAGE" => $msg,
			"TYPE" => ($success ? 'OK' : 'ERROR'),
		));
	}
}
$tabControl->begin();
?>

<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.cards.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.responsive.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.containers.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.tables.min.css">

<form method="post"
			action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
	<?php
	echo bitrix_sessid_post();
	$tabControl->beginNextTab();
	$optiAlgorithmJpeg = [
		'jpegoptim' => 'Jpegoptim',
	];
	$optiAlgorithmPng = [
		'optipng' => 'Optipng',
	];
	$resizeAlgorithm = [
		BX_RESIZE_IMAGE_PROPORTIONAL => Loc::getMessage('LABEL_SETTING_OG_BX_RESIZE_IMAGE_PROPORTIONAL'),
		BX_RESIZE_IMAGE_EXACT => Loc::getMessage('LABEL_SETTING_OG_BX_RESIZE_IMAGE_EXACT'),
		BX_RESIZE_IMAGE_PROPORTIONAL_ALT => Loc::getMessage('LABEL_SETTING_OG_BX_RESIZE_IMAGE_PROPORTIONAL_ALT'),
	];
	?>
	<tr class="heading">
		<td colspan="2">
			<b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', array('#MODULE#' => 'JPEG')) ?></b>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_ALGORITHM_SELECT') ?>:</label>
		</td>
		<td width="60%">
			<select name="opti_algorithm_jpeg">
				<?
				$selectAlgorithmJpeg = Option::get($curModuleName, "opti_algorithm_jpeg");
				foreach ($optiAlgorithmJpeg as $k => $v) { ?>
					<option value="<?= $k ?>" <?= ($k == $selectAlgorithmJpeg ? 'selected' : '') ?>><?= $v ?></option>
				<? } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="path_to_jpegoptim">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_JPEGOPTI") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="text"
						 size="50"
						 name="path_to_jpegoptim"
						 value="<?= Option::get($curModuleName, "path_to_jpegoptim", '/usr/bin'); ?>"
			/> /jpegoptim
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="jpegoptim_compress">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_JPEG_COMPRESS") ?>:
			</label>
		</td>
		<td width="60%">
			<select name="jpegoptim_compress">
				<?
				$jpgCompress = Option::get($curModuleName, "jpegoptim_compress", '80');
				for ($i = 0; $i <= 100; $i += 5) { ?>
					<option value="<?= $i ?>" <?= ($i == $jpgCompress ? 'selected' : '') ?>><?= $i ?></option>
				<? } ?>
			</select>
			<!--            <input type="text"-->
			<!--                   name="jpegoptim_compress"-->
			<!--                   value="--><? //=Option::get($curModuleName, "jpegoptim_compress", '80');?><!--"-->
			<!--            />-->
		</td>
	</tr>

	<tr>
		<td width="40%">
			<label for="enable_element">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_JPEG_PROGRESSIVE") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="checkbox"
						 name="jpeg_progressive"
						 value="Y"
				<?
				if (Option::get($curModuleName, "jpeg_progressive") == 'Y') {
					echo 'checked';
				}
				?>
			/>
		</td>
	</tr>


	<tr class="heading">
		<td colspan="2">
			<b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_SETTINGS', array('#MODULE#' => 'PNG')) ?></b>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_ALGORITHM_SELECT') ?>:</label>
		</td>
		<td width="60%">
			<select name="opti_algorithm_png">
				<?
				$selectAlgorithmPng = Option::get($curModuleName, "opti_algorithm_png");
				foreach ($optiAlgorithmPng as $k => $v) { ?>
					<option value="<?= $k ?>" <?= ($k == $selectAlgorithmPng ? 'selected' : '') ?>><?= $v ?></option>
				<? } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="path_to_optipng">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PATH_PNGOPTI") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="text"
						 size="50"
						 name="path_to_optipng"
						 value="<?= Option::get($curModuleName, "path_to_optipng", '/usr/bin'); ?>"
			/> /optipng
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="optipng_compress">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_PNG_COMPRESS") ?>:
			</label>
		</td>
		<td width="60%">
			<select name="optipng_compress">
				<?
				$pngCompress = Option::get($curModuleName, "optipng_compress", '3');
				for ($i = 1; $i <= 7; $i++) { ?>
					<option value="<?= $i ?>" <?= ($i == $pngCompress ? 'selected' : '') ?>><?= $i ?></option>
				<? } ?>
			</select>
		</td>
	</tr>


	<tr class="heading">
		<td colspan="2">
			<b><?= Loc::getMessage('D2F_IMAGECOMPRESS_HEADING_TEXT_BASE_SETTINGS') ?></b>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="enable_element">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_ELEMENT") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="checkbox"
						 name="enable_element"
						 value="Y"
				<?
				if (Option::get($curModuleName, "enable_element") == 'Y') {
					echo 'checked';
				}
				?>
			/>
		</td>
	</tr>

	<tr>
		<td width="40%">
			<label for="enable_section">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_SECTION") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="checkbox"
						 name="enable_section"
						 value="Y"
				<?
				if (Option::get($curModuleName, "enable_section") == 'Y') {
					echo 'checked';
				}
				?>
			/>
		</td>
	</tr>

	<tr>
		<td width="40%">
			<label for="enable_resize">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_RESIZE") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="checkbox"
						 name="enable_resize"
						 value="Y"
				<?
				if (Option::get($curModuleName, "enable_resize") == 'Y') {
					echo 'checked';
				}
				?>
			/>
		</td>
	</tr>

	<tr>
		<td width="40%">
			<label for="enable_save">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_ENABLE_SAVE") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="checkbox"
						 name="enable_save"
						 value="Y"
				<?
				if (Option::get($curModuleName, "enable_save") == 'Y') {
					echo 'checked';
				}
				?>
			/>
		</td>
	</tr>

	<tr>
		<td width="40%">
			<label for="cnt_step">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CNT_STEP") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="text"
						 name="cnt_step"
						 value="<?= Option::get($curModuleName, "cnt_step", 30) ?>"
			/>
		</td>
	</tr>

	<tr>
		<td width="40%">
			<label for="cnt_step">
				<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_CHMOD") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="text"
						 name="change_chmod"
						 value="<?= Option::get($curModuleName,'change_chmod', '0777') ?>"
			/>
		</td>
	</tr>


	<tr class="heading">
		<td colspan="2">
			<b><?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_HEADING") ?></b>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="resize_image_enable">
				<?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_ENABLE") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="checkbox"
						 name="resize_image_enable"
						 value="Y"
				<?
				if (Option::get($curModuleName, "resize_image_enable") == 'Y') {
					echo 'checked';
				}
				?>
			/>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="resize_image_width">
				<?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_WIDTH") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="text"
						 name="resize_image_width"
						 value="<?= Option::get($curModuleName, "resize_image_width") ?>"
			/>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="resize_image_height">
				<?= Loc::getMessage("D2F_COMPRESS_OPTIONS_RESIZE_IMAGE_HEIGHT") ?>:
			</label>
		</td>
		<td width="60%">
			<input type="text"
						 name="resize_image_height"
						 value="<?= Option::get($curModuleName, "resize_image_height") ?>"
			/>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label><?= Loc::getMessage('D2F_IMAGECOMPRESS_OPTIONS_RESIZE_IMAGE_ALGORITHM_SELECT') ?>:</label>
		</td>
		<td width="60%">
			<?
			$selectResizeAlgorithm = Option::get($curModuleName, "resize_image_algorithm");
			foreach ($resizeAlgorithm as $k => $v) { ?>
				<label>
					<input type="radio" name="resize_image_algorithm"
								 value="<?= $k ?>" <?= ($selectResizeAlgorithm == $k) ? 'checked' : '' ?>>
					<?= $v ?>
				</label>
				<br>
			<? } ?>
		</td>
	</tr>


	<? $tabControl->BeginNextTab(); ?>
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
							<h2 id="yaPay" class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_TEXT'); ?></h2>
							<iframe
								src="https://money.yandex.ru/quickpay/shop-widget?writer=seller&targets=%D0%9F%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%BA%D0%B0%20%D0%BE%D0%B1%D0%BD%D0%BE%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BC%D0%BE%D0%B4%D1%83%D0%BB%D0%B5%D0%B9&targets-hint=&default-sum=500&button-text=14&payment-type-choice=on&mobile-payment-type-choice=on&hint=&successURL=&quickpay=shop&account=410011413398643"
								width="450" height="228" frameborder="0" allowtransparency="true" scrolling="no"></iframe>
							<h2 id="morePay"
									class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_ALL_TEXT'); ?></h2>
							<table class="c-table">
								<tbody class="c-table__body c-table--striped">
								<tr class="c-table__row">
									<td class="c-table__cell">Yandex.Money</td>
									<td class="c-table__cell">410011413398643</td>
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
									<td class="c-table__cell"><a href="https://www.paypal.me/darkfriend" target="_blank">paypal.me/@darkfriend</a>
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
									<td class="c-table__cell">bitcoincash:qrl8p6jxgpkeupmvyukg6mnkeafs9fl5dszft9fw9w</td>
								</tr>
								</tbody>
							</table>
							<h2 id="moreThanks"
									class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_OTHER_TEXT'); ?></h2>
							<?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_OTHER_TEXT_S'); ?>
						</div>
					</div>
					<div class="o-grid__cell o-grid__cell--width-30">
						<h2 id="moreThanks" class="c-heading u-large"><?= Loc::getMessage('LABEL_TITLE_HELP_DONATE_FOLLOW'); ?></h2>
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

	<?php
	$tabControl->buttons();
	?>
	<input type="submit"
				 name="save"
				 value="<?= Loc::getMessage("MAIN_SAVE") ?>"
				 title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
				 class="adm-btn-save"
	/>
	<input type="submit"
				 name="test_module"
				 value="<?= Loc::getMessage("D2F_COMPRESS_REFERENCES_TEST_BTN") ?>"
	/>
	<? /* ?>
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
    />
    <? */ ?>
	<? $tabControl->end(); ?>
</form>