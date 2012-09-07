<?php
//	die('Comment die()');

	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	try {

		Site::initMicroTime();

		Item::dao()->uncacheLists();
		Property::dao()->uncacheLists();

		Item::dao()->setItemList();
		Property::dao()->setPropertyList();

		try {

			$dicts = array(
				Bind2Item::dao(),
				ItemPermission::dao(),
				ElementPermission::dao(),
				Group::dao(),
				ServiceSection::dao(),
				BannerRubric::dao(),
				BannerCategory::dao(),
				BannerDuration::dao(),
				PaymentState::dao(),
				SiteUserCategory::dao(),
				LoadingState::dao(),
				BuildingCondition::dao(),
				FlatCondition::dao(),
				CountryType::dao(),
				RoomNumber::dao(),
				PlotType::dao(),
				CommerceRubric::dao(),
				CommerceType::dao(),
				GarageType::dao(),
				AreaUnit::dao(),
				PriceUnit::dao(),
				Lavatory::dao(),
				LegalForm::dao(),
				Manager::dao(),
//				PageTemplate::dao(),
			);

			foreach ($dicts as $dao) {
				$dao->uncacheLists();
			}

		} catch (BaseException $e) {
			echo ErrorMessageUtils::printMessage($e);
		}

		Site::generateAuto();
		echo 'Generate classes OK<br>';
		exit();

	} catch (BaseException $e) {
		echo ErrorMessageUtils::printMessage($e);
	}
?>