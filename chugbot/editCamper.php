<?php
    session_start();
    require_once 'addEdit.php';
    require_once 'formItem.php';
    camperBounceToLogin();
    
    $editCamperPage = new EditPage(
        "Review Camper Information",
        "Please review your information below, and make any necessary edits.",
        "campers", "camper_id"
    );
    $editCamperPage->addSecondParagraph("Then click <b>Update Chugim</b> to update your chug preferences, or click <b>Save and Exit</b> to save your changes without updating chug preferences.");
    $editCamperPage->setAlternateResultString("Please review your information below, make any edits needed, and then click <b>Choose Chugim</b> to make your chug rankings.");
    $editCamperPage->addColumn("first");
    $editCamperPage->addColumn("last");
    $editCamperPage->addColumn("email");
    $editCamperPage->addColumn("session_id", true, true);
    $editCamperPage->addColumn("edah_id", true, true);
    $editCamperPage->addColumn("bunk_id", false, true);
    $editCamperPage->addColumn("needs_first_choice", false, true, 0);
    $editCamperPage->addColumn("inactive", false, true, 0);
    $editCamperPage->setSubmitAndContinueTarget("rankCamperChoices.html", "Update Chugim");
    $editCamperPage->setSaveAndReturnLabel("Save and Exit");

    $editCamperPage->handleSubmit();

    $firstNameField = new FormItemSingleTextField("First Name", true, "first", 0);
    $firstNameField->setInputType("text");
    $firstNameField->setInputClass("element text medium");
    $firstNameField->setInputMaxLength(255);
    $firstNameField->setInputValue($editCamperPage->columnValue("first"));
    $firstNameField->setPlaceHolder("First Name");
    $firstNameField->setError($editCamperPage->errForColName("first"));
    $editCamperPage->addFormItem($firstNameField);

    $lastNameField = new FormItemSingleTextField("Last Name", true, "last", 1);
    $lastNameField->setInputType("text");
    $lastNameField->setInputClass("element text medium");
    $lastNameField->setInputMaxLength(255);
    $lastNameField->setInputValue($editCamperPage->columnValue("last"));
    $lastNameField->setPlaceHolder("Last Name");
    $lastNameField->setError($editCamperPage->errForColName("last"));
    $editCamperPage->addFormItem($lastNameField);

    $emailField = new FormItemSingleTextField("Email address", true, "email", 2);
    $emailField->setInputType("email");
    $emailField->setInputClass("element text medium");
    $emailField->setInputMaxLength(255);
    $emailField->setInputValue($editCamperPage->columnValue("email"));
    $emailField->setPlaceHolder("Email address");
    $emailField->setError($editCamperPage->errForColName("email"));
    $emailField->setGuideText("Please include an email address (you can use the same email for more than one camper)");
    $editCamperPage->addFormItem($emailField);

    $sessionIdVal = $editCamperPage->columnValue("session_id"); // May be NULL.
    $sessionDropDown = new FormItemDropDown("Session", true, "session_id", 3);
    $sessionDropDown->setGuideText("Choose your camp session.");
    $sessionDropDown->setInputClass("element select medium");
    $sessionDropDown->setError($editCamperPage->errForColName("session_id"));
    $sessionDropDown->setInputSingular("session");
    $sessionDropDown->setColVal($sessionIdVal);
    $sessionDropDown->fillDropDownId2Name(
        $editCamperPage->dbErr,
        "session_id", "sessions"
    );
    $editCamperPage->addFormItem($sessionDropDown);
    
    $edahIdVal = $editCamperPage->columnValue("edah_id"); // May be NULL.
    $edahDropDown = new FormItemDropDown("Edah", true, "edah_id", 4);
    $edahDropDown->setGuideText("Choose your Edah!");
    $edahDropDown->setError($editCamperPage->errForColName("edah_id"));
    $edahDropDown->setInputSingular("edah");
    $edahDropDown->setInputClass("element select medium");
    $edahDropDown->setColVal($edahIdVal);
    $edahDropDown->fillDropDownId2Name(
        $editCamperPage->dbErr,
        "edah_id", "edot"
    );
    $editCamperPage->addFormItem($edahDropDown);
    
    $bunkIdVal = $editCamperPage->columnValue("bunk_id"); // May be NULL.
    $bunkDropDown = new FormItemConstrainedDropDown(
        "Bunk/Tzrif", false, "bunk_id", 5,
        "SELECT b.bunk_id id_val, b.name name_val FROM bunks b, " .
        "bunk_instances i WHERE b.bunk_id = i.bunk_id AND i.edah_id = ?"
    );
    $bunkDropDown->setGuideText("Choose your bunk (you can leave this blank if you do not know it yet!).  You must choose your Edah first.");
    $bunkDropDown->setInputClass("element select medium");
    $bunkDropDown->setParentIdAndName("edah_id", "Edah");
    $bunkDropDown->setColVal($bunkIdVal);
    $editCamperPage->addFormItem($bunkDropDown);
    
    // Add two fields that are only visible to staff.  These apply only to the edit page, not the add
    // page.
    $needsFirstChoiceVal = $editCamperPage->columnValue("needs_first_choice");
    $needsFirstChoiceBox = new FormItemCheckBox("Needs first choice", false, "needs_first_choice", 6);
    $needsFirstChoiceBox->setGuideText("Check this box if this camper should always get their first choice chug.");
    $needsFirstChoiceBox->setStaffOnly(true);
    $needsFirstChoiceBox->setInputValue($needsFirstChoiceVal);
    $editCamperPage->addFormItem($needsFirstChoiceBox);
    
    $inactiveVal = $editCamperPage->columnValue("inactive");
    $inactiveBox = new FormItemCheckBox("Inactive", false, "inactive", 7);
    $inactiveBox->setGuideText("If you check this box, this camper will not be assigned.");
    $inactiveBox->setStaffOnly(true);
    $inactiveBox->setInputValue($inactiveVal);
    $editCamperPage->addFormItem($inactiveBox);
    
    $editCamperPage->renderForm();
    
    ?>

    