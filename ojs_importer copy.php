<?php

/*
	information missing:
		number of issue;
		cover image;
		data_published;
		open_access;
		discipline;
		open_access;
		article information about the front matter;
		*** Do I need to specify the format of the abstract? ***
		*** Should I include the article content? ***
*/

	include_once("/Users/zhao0677/Projects/util-lib/commonFunc.php");


	include_once("/Users/zhao0677/Projects/util-lib/PHPEXCEL_includes.php");

	Class OJSImport extends AbstractPHPExcel 
	{
		private $files;
		private $data;
		private $type;  // article or issue
		private $hasHeading;
		private $cleanData;

		function getData()
		{
			return $this->data;
		}

		function setData($data)
		{
			$this->data = $data;
		}

		function getFiles()
		{
			return $this->files;
		}

		function setFiles($files)
		{
			if(is_array($files) == false && is_string($files) == false)
			{
				echo "\nThe parameter should be an array or a string!\n";
				exit();
			}
			$this->files = $files;
		}

		function getHasHeading()
		{
			return $this->hasHeading;
		}

		function setHasHeading($hasHeading = false)
		{
			$this->hasHeading = $hasHeading;
		}

		function getType()
		{
			return $this->type;
		}

		function setType($type)
		{
			$this->type = $type;
		}

		function getCleanData()
		{
			return $this->cleanData;
		}

		function setCleanData($data)
		{
			$this->cleanData = $data;
		}

		function loadOjsDataFromFiles()
		{
			$files = $this->getFiles();

			if(!isset($files) || $files == "")
			{
				echo "\nThis object has no files set.\n";
				return;
			}

			if(is_array($files)){

				$data = array();
				foreach ($files as $key => $file) {
					$data[] = $this->getArrayDataFromExcel($file, $this->getHasHeading());
				}
			}
			elseif(is_string($files)){
				$data = $this->getArrayDataFromExcel($files, $this->getHasHeading());
			}
			else{
				echo "\nThe file type is not set up correctly!\n";
				exit();
			}
			$this->setData($data);
		}

		function getXmlFromExcel()
		{
			if(!isset($this->data))
				$this->loadOjsDataFromFiles();

			$this->getXmlFromArray($dtd);
		}

		// For convenience, $dtd is the prefix of the dtd schema file name:
		// example: $dtd = "native" for the file of native.dtd
		// The dtd file should be in the same directory
		function getXmlFromArray($dtd)
		{
			$this->loadOjsDataFromFiles();
			$type = $this->getType();
			$this->cleanData();
			$cleanData = $this->getCleanData();
			$temp = explode(".",$dtd);
			$dtd = $temp[0];

			if(isset($cleanData) && count($cleanData) > 0)
			{

				// start buliding the xml file:
				$imp = new DOMImplementation;
				$dtd = $imp->createDocumentType($dtd, '', $dtd.".dtd");
				$dom = $imp->createDocument("issues", "", $dtd);
				$dom->encoding = 'UTF-8';
				$dom->standalone = false;

			    $issues = $dom->createElement("issues");
			    $dom->appendChild($issues);

				foreach ($cleanData as $key => $value) {

					$key_arr = explode("_",$key);

					$issue = $dom->createElement("issue");
					$child = $issues->appendChild($issue);
					$child->setAttributeNode(new DOMAttr('current', 'false'));
					$child->setAttributeNode(new DOMAttr('published', 'true'));


					$issueTitle = $dom->createElement("title");
					$child = $issue->appendChild($issueTitle);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));
					$issueTitleText = $dom->createTextNode($key_arr[0]." ".$key_arr[2]);
					$issueTitle->appendChild($issueTitleText);


					$issueVolume = $dom->createElement("volume");
					$issueVolText = $dom->createTextNode($key_arr[1]);
					$issueVolume->appendChild($issueVolText);
					$issue->appendChild($issueVolume);

					// ** note: is the number associated with the issue or the specific article?

					// $issueNumber = $dom->createElement("number");
					// $issueNumText = $dom->createTextNode($value[number] == "" ? 0 : $value[number]);
					// $issueNumber->appendChild($issueNumText);

					$issueYear = $dom->createElement("year");
					$issueYearText = $dom->createTextNode($key_arr[2]);
					$issueYear->appendChild($issueYearText);
					$issue->appendChild($issueYear);

					/* 
						Note:  should we add empty nodes for these elements:
						cover is not set up here
						date_published is not set up here
						open_access is not set up here
					*/

					$issueCover = $dom->createElement("cover");
					//$issueCoverText = $dom->createTextNode($key_arr[1]);
					//$issueCover->appendChild($issueCoverText);
					$child = $issue->appendChild($issueCover);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));
					$coverImage = $dom->createElement("image");
					$issueCover->appendChild($coverImage);
					$imageHref = $dom->createElement("href");
					$child = $coverImage->appendChild($imageHref);
					$child->setAttributeNode(new DOMAttr('src','/34_summer_2013/34_Spring-Summer_2013_Front_Cover_Small.jpg'));
					$child->setAttributeNode(new DOMAttr('mime_type','application/jpg'));



					$issueDate = $dom->createElement("date_published");
					//$issueYearText = $dom->createTextNode($key_arr[1]);
					$issueDateText = $dom->createTextNode("2014-09-18");
					$issueDate->appendChild($issueDateText);
					$issue->appendChild($issueDate);

					$issueAccess = $dom->createElement("open_access");
					//$issueYearText = $dom->createTextNode($key_arr[1]);
					//$issueYear->appendChild($issueYearText);
					$issue->appendChild($issueAccess);

					// create the front matter:
					$section = $dom->createElement("section");
					$issue->appendChild($section);

					$titleFront = $dom->createElement("title");
					$titleFrontText = $dom->createTextNode("Front Matter");
					$titleFront->appendChild($titleFrontText);
					$child = $section->appendChild($titleFront);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));

					$abbrev = $dom->createElement("abbrev");
					$abbrevText = $dom->createTextNode("FRO");
					$abbrev->appendChild($abbrevText);
					$child = $section->appendChild($abbrev);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));

					// *** note: Skip the front matter article
					$article = $dom->createElement("article");
					$child = $section->appendChild($article);
					$child->setAttributeNode(new DOMAttr('language','en'));
					$child->setAttributeNode(new DOMAttr('locale','en_US'));

					$articleTitle = $dom->createElement("title");
					$child = $article->appendChild($articleTitle);
					$articleTitleText = $dom->createTextNode("Front Matter");
					$articleTitle->appendChild($articleTitleText);

					$articlePriAuthor = $dom->createElement("author");
					$child = $article->appendChild($articlePriAuthor);
					$child->setAttributeNode(new DOMAttr('primary_contact','true'));

					$lastName = $dom->createElement("lastname");
					$lastNameText = $dom->createTextNode("Zhao");
					$lastName->appendChild($lastNameText);
					$articlePriAuthor->appendChild($lastName);

					$firstName = $dom->createElement("firstname");
					$firstNameText = $dom->createTextNode("Tao");
					$firstName->appendChild($firstNameText);
					$articlePriAuthor->appendChild($firstName);

					$affiliation = $dom->createElement("affiliation");
					$affiliationText = $dom->createTextNode("University of Oklahoma");
					$affiliation->appendChild($affiliationText);
					$articlePriAuthor->appendChild($affiliation);

					$country = $dom->createElement("country");
					$countryVal = (isset($articleInfo[Author1_Country]) && $articleInfo[Author1_Country] != "") ? $articleInfo[Author1_Country] : "US";
					$countryText = $dom->createTextNode($countryVal);
					$country->appendChild($countryText);
					$articlePriAuthor->appendChild($country);

					// $email = $dom->createElement("email");
					// $emailText = $dom->createTextNode($articleInfo[Author1_Email]);
					// $email->appendChild($emailText);
					// $articlePriAuthor->appendChild($email);


					$articleGalley = $dom->createElement("galley");
					$child = $article->appendChild($articleGalley);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));

					$articleLabel = $dom->createElement("label");
					// right now all the articles are PDF files
					$articleLabelText = $dom->createTextNode("PDF");
					$articleLabel->appendChild($articleLabelText);
					$articleGalley->appendChild($articleLabel);

					$articleFile = $dom->createElement("file");
					$articleGalley->appendChild($articleFile);

					$fileHref = $dom->createElement("href");
					$child = $articleFile->appendChild($fileHref);
					$child->setAttributeNode(new DOMAttr('src','/34_summer_2013/34_Spring-Summer_2013_Front_Matter.pdf'));
					$child->setAttributeNode(new DOMAttr('mime_type','application/pdf'));


					// create the section for articles:
					$section = $dom->createElement("section");
					$issue->appendChild($section);

					$titleArticles = $dom->createElement("title");
					$titleArticlesText = $dom->createTextNode("Articles");
					$titleArticles->appendChild($titleArticlesText);
					$child = $section->appendChild($titleArticles);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));

					$abbrev = $dom->createElement("abbrev");
					$abbrevText = $dom->createTextNode("ART");
					$abbrev->appendChild($abbrevText);
					$child = $section->appendChild($abbrev);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));


					// create the nodes for the articles in this issue:
					foreach ($value as $key => $articleInfo) {

						$article = $dom->createElement("article");
						$child = $section->appendChild($article);
						$child->setAttributeNode(new DOMAttr('language','en'));
						$child->setAttributeNode(new DOMAttr('locale','en_US'));

						$articleTitle = $dom->createElement("title");
						$child = $article->appendChild($articleTitle);
						$child->setAttributeNode(new DOMAttr('locale','en_US'));
						$articleTitleText = $dom->createTextNode($articleInfo[Title]);
						$articleTitle->appendChild($articleTitleText);

						$articleAbstract = $dom->createElement("Abstract");
						$child = $article->appendChild($articleAbstract);
						$child->setAttributeNode(new DOMAttr('locale','en_US'));
						$articleAbstractText = $dom->createTextNode($articleInfo['Abstract']);
						$articleAbstract->appendChild($articleAbstractText);

						$articleIndex = $dom->createElement("indexing");
						$child = $article->appendChild($articleIndex);
						$child->setAttributeNode(new DOMAttr('locale','en_US'));

						$articleDiscipline = $dom->createElement("discipline");
						$child = $articleIndex->appendChild($articleDiscipline);
						$child->setAttributeNode(new DOMAttr('Locale','en_US'));
						// the content of discipline is missing for most articles.
						$articleDisciplineText = $dom->createTextNode("Education; Literature Education");
						$articleDiscipline->appendChild($articleDisciplineText);


						$articleGalley = $dom->createElement("galley");
						$child = $article->appendChild($articleGalley);
						$child->setAttributeNode(new DOMAttr('locale','en_US'));

						$articleLabel = $dom->createElement("label");
						// right now all the articles are PDF files
						$articleLabelText = $dom->createTextNode("PDF");
						$articleLabel->appendChild($articleLabelText);
						$articleGalley->appendChild($articleLabel);

						$articleFile = $dom->createElement("file");
						$articleGalley->appendChild($articleFile);

						$fileHref = $dom->createElement("href");
						$child = $articleFile->appendChild($fileHref);
						$child->setAttributeNode(new DOMAttr('src','/34_summer_2013/'.$articleInfo[Filename]));
						$child->setAttributeNode(new DOMAttr('mime_type','application/pdf'));


						$articlePriAuthor = $dom->createElement("author");
						$child = $article->appendChild($articlePriAuthor);
						$child->setAttributeNode(new DOMAttr('primary_contact','true'));

						$lastName = $dom->createElement("lastname");
						$lastNameText = $dom->createTextNode($articleInfo[Author1_Last]);
						$lastName->appendChild($lastNameText);
						$articlePriAuthor->appendChild($lastName);

						$middleName = $dom->createElement("middlename");
						$middleNameText = $dom->createTextNode($articleInfo[Author1_Middle]);
						$middleName->appendChild($middleNameText);
						$articlePriAuthor->appendChild($middleName);

						$firstName = $dom->createElement("firstname");
						$firstNameText = $dom->createTextNode($articleInfo[Author1_First]);
						$firstName->appendChild($firstNameText);
						$articlePriAuthor->appendChild($firstName);

						$suffix = $dom->createElement("suffix");
						$suffixText = $dom->createTextNode($articleInfo[Author1_Suffix]);
						$suffix->appendChild($suffixText);
						$articlePriAuthor->appendChild($suffix);

						$affiliation = $dom->createElement("affiliation");
						$affiliationText = $dom->createTextNode($articleInfo[Author1_Affiliation]);
						$affiliation->appendChild($affiliationText);
						$articlePriAuthor->appendChild($affiliation);

						$country = $dom->createElement("country");
						$countryVal = (isset($articleInfo[Author1_Country]) && $articleInfo[Author1_Country] != "") ? $articleInfo[Author1_Country] : "US";
						$countryText = $dom->createTextNode($countryVal);
						$country->appendChild($countryText);
						$articlePriAuthor->appendChild($country);

						$email = $dom->createElement("email");
						$emailText = $dom->createTextNode($articleInfo[Author1_Email]);
						$email->appendChild($emailText);
						$articlePriAuthor->appendChild($email);

						// build more author nodes
						foreach ($articleInfo as $articleIndex => $articleData) {
							if(strpos($articleIndex,"Author1") !== true && strpos($articleIndex,"Author") !== false && strpos($articleIndex,"_Middle") !== false)
							{
			
								$authorIndex = $articleIndex[6];
								if($articleInfo["Author".$authorIndex."_Last"] != "" && $articleInfo["Author".$authorIndex."_First"] != "" )
								{
									$author = $dom->createElement("author");
									$article->appendChild($author);

									$lastName = $dom->createElement("Lastname");
									$lastNameText = $dom->createTextNode($articleInfo["Author".$authorIndex."_Last"]);
									$lastName->appendChild($lastNameText);
									$author->appendChild($lastName);

									$middleName = $dom->createElement("middlename");
									$middleNameText = $dom->createTextNode($articleInfo["Author".$authorIndex."_Middle"]);
									$middleName->appendChild($middleNameText);
									$author->appendChild($middleName);

									$firstName = $dom->createElement("firstname");
									$firstNameText = $dom->createTextNode($articleInfo["Author".$authorIndex."_First"]);
									$firstName->appendChild($firstNameText);
									$author->appendChild($firstName);

									$suffix = $dom->createElement("suffix");
									$suffixText = $dom->createTextNode($articleInfo["Author".$authorIndex."_Suffix"]);
									$suffix->appendChild($suffixText);
									$author->appendChild($suffix);

									$affiliation = $dom->createElement("affiliation");
									$affiliationText = $dom->createTextNode($articleInfo["Author".$authorIndex."_Affiliation"]);
									$affiliation->appendChild($affiliationText);
									$author->appendChild($affiliation);

									$country = $dom->createElement("country");
									$countryVal = (isset($articleInfo["Author".$authorIndex."_Country"]) && $articleInfo["Author".$authorIndex."_Country"] != "") ? $articleInfo["Author".$authorIndex."_Country"] : "US";
									$countryText = $dom->createTextNode($countryVal);
									$country->appendChild($countryText);
									$author->appendChild($country);

									$email = $dom->createElement("email");
									$emailText = $dom->createTextNode($articleInfo["Author".$authorIndex."_Email"]);
									$email->appendChild($emailText);
									$author->appendChild($email);
								}
								else
								{
									continue;
								}
							}

							else
							{
								continue;
							}
						}

					}
				}

			    /* get the xml printed */
			    echo $dom->save("./output.xml");
			}
		}

		function getUserXmlFromArray($dtd)
		{
			$this->loadOjsDataFromFiles();
			$type = $this->getType();
			$this->cleanUserData();
			$cleanData = $this->getCleanData();
			$temp = explode(".",$dtd);
			$dtd = $temp[0];

			if(isset($cleanData) && count($cleanData) > 0)
			{

				// start buliding the xml file:
				$imp = new DOMImplementation;
				$dtd = $imp->createDocumentType($dtd, '', $dtd.".dtd");
				$dom = $imp->createDocument("users", "", $dtd);
				$dom->encoding = 'UTF-8';
				$dom->standalone = false;

			    $users = $dom->createElement("users");
			    $dom->appendChild($users);

			    //$count = 0;
			    //$total = count($cleanData);

				foreach ($cleanData as $key => $value) {


					$user = $dom->createElement("user");
					$child = $users->appendChild($user);
					$users->appendChild($user);

					$username = $dom->createElement("username");
					$child = $user->appendChild($username);
					$usernameText = $dom->createTextNode($value["Username"]);
					$user->appendChild($usernameText);

					// ** note: password may only appear in the exported user xml files 
					// $password = $dom->createElement("password");
					// $pwText = $dom->createTextNode($key_arr[1]);
					// $password->appendChild($pwText);
					// $child = $user->appendChild($password);

					$firstName = $dom->createElement("first_name");
					$firstNameText = $dom->createTextNode($value["Firstname"]);
					$firstName->appendChild($firstNameText);
					$user->appendChild($firstName);

					$middleName = $dom->createElement("middle_name");
					$middleNameText = $dom->createTextNode($value["Middlename"]);
					$middleName->appendChild($middleNameText);
					$user->appendChild($middleName);

					$lastName = $dom->createElement("last_name");
					$lastNameText = $dom->createTextNode($value["Lastname"]);
					$lastName->appendChild($lastNameText);
					$user->appendChild($lastName);

					$initials = $dom->createElement("initials");
					$initialsText = $dom->createTextNode($value["Initials"]);
					$initials->appendChild($initialsText);
					$user->appendChild($initials);

					$gender = $dom->createElement("gender");
					$genderText = $dom->createTextNode($value["Gender"]);
					$gender->appendChild($genderText);
					$user->appendChild($gender);

					$email = $dom->createElement("email");
					$emailText = $dom->createTextNode($key."tao@ou.edu");
					$email->appendChild($emailText);
					$user->appendChild($email);

					/* get the xml printed */
					// $count = $key + 1;
					// if($count % 50 == 0 || $total == $count){
				    	
				 //    	if($count % 50 == 0)
				 //    		$file_count = $count/50;
				 //    	else
				 //    		$file_count = $count/50 + 1;

				 //    	echo "\nfilecount = ".$file_count."\n";
				 //    	//$dom->save("./output_users_".$file_count.".xml");
				 //    }
					$dom->save("./output_users.xml");
					// ** note: there might be multiple roles for one user and this feature is not full implemented here:
					// $role = $dom->createElement("role");
					// $roleText = $dom->createTextNode("author");
					// $role->appendChild($roleText);
					// $user->appendChild($role);

				}

			}
		}

		function cleanData()
		{
			$cleanData = Array();
			$data = $this->data;printInfo($data);die;
			foreach ($data as $key => $value) {

				if($value[Issue] == "" || $value[Volume] == "" || $value[Year] == "")
					continue;

				$issue = $value[Issue]."_".$value[Volume]."_".$value[Year]; 

				if($value[Type] == 'Cover'){
					if()
				} 

				if($value[Filename] != "" && $value[Volume] != ""  && $value[Year] != "" && $value[Issue] != "" 
					&& $value[Author1_First] != "" && $value[Author1_Last] != ""){
					
					unset($value[Issue]);
					unset($value[Volume]);
					unset($value[Year]);
					$cleanData[$issue][] = $value;
				}
			}
			$this->setCleanData($cleanData);

		}

		function cleanUserData()
		{
			$cleanData = Array();
			$data = $this->data;
			foreach ($data as $index => $userInfo) {

				if($userInfo["Firstname"] != "" && $userInfo["Lastname"] != ""  && $userInfo["Email"] != "")
				{
					$cleanData[] = $userInfo;
				}
				else
				{
					continue;
				}
			}
			$this->setCleanData($cleanData);
		}

		function printExcelData()
		{
			printInfo($this->getData());
		}

		function printCleanData()
		{
			printInfo($this->getCleanData());
		}

	}

	if(count($argv) <= 1)
	{
		echo "No argument is specified!\n";
		exit();
	}

	$ojs = new OJSImport();
	
	$ojs->setHasHeading(true);

	//$ojs->loadOjsDataFromFiles();

	//$ojs->cleanData();

	if($argv[1] == '1'){
		$ojs->setFiles("/Users/zhao0677/Projects/OJS-import/OJS_Vols33-34.xlsx");
		$ojs->getXmlFromArray("native.dtd");
	}
	else{
		$ojs->setFiles("/Users/zhao0677/Projects/OJS-import/ojsData_users.xlsx");
		$ojs->getUserXmlFromArray("users.dtd");
	}


