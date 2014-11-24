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

		private function addCoverInfo($dom, $issue, $filepath)
		{
			$issueCover = $dom->createElement("cover");
			$child = $issue->appendChild($issueCover);
			$child->setAttributeNode(new DOMAttr('locale','en_US'));
			$coverImage = $dom->createElement("image");
			$issueCover->appendChild($coverImage);
			$imageHref = $dom->createElement("href");
			$child = $coverImage->appendChild($imageHref);
			$coverHref = "./".$key."/".$filename;
			$child->setAttributeNode(new DOMAttr('src',$filepath));
			$child->setAttributeNode(new DOMAttr('mime_type','application/jpg'));

		}

		private function AddFrontInfo($dom, $issue,$filepath, $lastNameValue, $firstNameValue, $emailValue)
		{
			//$frontInfo = $value["FRO"];
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

			$lastName = $dom->createElement("lastname");
			$lastNameText = $dom->createTextNode($lastNameValue);
			$lastName->appendChild($lastNameText);
			$articlePriAuthor->appendChild($lastName);

			$firstName = $dom->createElement("firstname");
			$firstNameText = $dom->createTextNode($firstNameValue);
			$firstName->appendChild($firstNameText);
			$articlePriAuthor->appendChild($firstName);

			// $affiliation = $dom->createElement("affiliation");
			// $affiliationText = $dom->createTextNode("University of Oklahoma");
			// $affiliation->appendChild($affiliationText);
			// $articlePriAuthor->appendChild($affiliation);

			// $country = $dom->createElement("country");
			// $countryVal = (isset($articleInfo[Author1_Country]) && $articleInfo[Author1_Country] != "") ? $articleInfo[Author1_Country] : "US";
			// $countryText = $dom->createTextNode($countryVal);
			// $country->appendChild($countryText);
			// $articlePriAuthor->appendChild($country);

			$email = $dom->createElement("email");
			$emailText = $dom->createTextNode($emailValue);
			$email->appendChild($emailText);
			$articlePriAuthor->appendChild($email);


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
			$child->setAttributeNode(new DOMAttr('src',$filepath));
			$child->setAttributeNode(new DOMAttr('mime_type','application/pdf'));


			// // create the section for articles:
			// $section = $dom->createElement("section");
			// $issue->appendChild($section);

			// $titleArticles = $dom->createElement("title");
			// $titleArticlesText = $dom->createTextNode("Articles");
			// $titleArticles->appendChild($titleArticlesText);
			// $child = $section->appendChild($titleArticles);
			// $child->setAttributeNode(new DOMAttr('locale','en_US'));

			// $abbrev = $dom->createElement("abbrev");
			// $abbrevText = $dom->createTextNode("ART");
			// $abbrev->appendChild($abbrevText);
			// $child = $section->appendChild($abbrev);
			// $child->setAttributeNode(new DOMAttr('locale','en_US'));
		}

		private function addArticleAuthorInfo($dom, $article, $authInfo, $emailValue)
		{
			$articlePriAuthor = $dom->createElement("author");
			$child = $article->appendChild($articlePriAuthor);
			$child->setAttributeNode(new DOMAttr('primary_contact','true'));

			$lastName = $dom->createElement("lastname");
			$lastNameText = $dom->createTextNode($authInfo[Author1_Last]);
			$lastName->appendChild($lastNameText);
			$articlePriAuthor->appendChild($lastName);

			$middleName = $dom->createElement("middlename");
			$middleNameText = $dom->createTextNode($authInfo[Author1_Middle]);
			$middleName->appendChild($middleNameText);
			$articlePriAuthor->appendChild($middleName);

			$firstName = $dom->createElement("firstname");
			$firstNameText = $dom->createTextNode($authInfo[Author1_First]);
			$firstName->appendChild($firstNameText);
			$articlePriAuthor->appendChild($firstName);

			$suffix = $dom->createElement("suffix");
			$suffixText = $dom->createTextNode($authInfo[Author1_Suffix]);
			$suffix->appendChild($suffixText);
			$articlePriAuthor->appendChild($suffix);

			$affiliation = $dom->createElement("affiliation");
			$affiliationText = $dom->createTextNode($authInfo[Author1_Affiliation]);
			$affiliation->appendChild($affiliationText);
			$articlePriAuthor->appendChild($affiliation);

			$country = $dom->createElement("country");
			$countryVal = (isset($authInfo[Author1_Country]) && $authInfo[Author1_Country] != "") ? $authInfo[Author1_Country] : "US";
			$countryText = $dom->createTextNode($countryVal);
			$country->appendChild($countryText);
			$articlePriAuthor->appendChild($country);

			$email = $dom->createElement("email");
			$emailText = $dom->createTextNode($emailValue);
			$email->appendChild($emailText);
			$articlePriAuthor->appendChild($email);

			// build more author nodes
			foreach ($authInfo as $articleIndex => $articleData) 
			{
				if(strpos($articleIndex,"Author1") === false && strpos($articleIndex,"Author") !== false && strpos($articleIndex,"_Middle") !== false)
				{

					$authorIndex = $articleIndex[6];
					if($authInfo["Author".$authorIndex."_Last"] != "" && $authInfo["Author".$authorIndex."_First"] != "" )
					{
						$author = $dom->createElement("author");
						$article->appendChild($author);

						$lastName = $dom->createElement("lastname");
						$lastNameText = $dom->createTextNode($authInfo["Author".$authorIndex."_Last"]);
						$lastName->appendChild($lastNameText);
						$author->appendChild($lastName);

						$middleName = $dom->createElement("middlename");
						$middleNameText = $dom->createTextNode($authInfo["Author".$authorIndex."_Middle"]);
						$middleName->appendChild($middleNameText);
						$author->appendChild($middleName);

						$firstName = $dom->createElement("firstname");
						$firstNameText = $dom->createTextNode($authInfo["Author".$authorIndex."_First"]);
						$firstName->appendChild($firstNameText);
						$author->appendChild($firstName);

						$suffix = $dom->createElement("suffix");
						$suffixText = $dom->createTextNode($authInfo["Author".$authorIndex."_Suffix"]);
						$suffix->appendChild($suffixText);
						$author->appendChild($suffix);

						$affiliation = $dom->createElement("affiliation");
						$affiliationText = $dom->createTextNode($authInfo["Author".$authorIndex."_Affiliation"]);
						$affiliation->appendChild($affiliationText);
						$author->appendChild($affiliation);

						$country = $dom->createElement("country");
						$countryVal = (isset($authInfo["Author".$authorIndex."_Country"]) && $authInfo["Author".$authorIndex."_Country"] != "") ? $articleInfo["Author".$authorIndex."_Country"] : "US";
						$countryText = $dom->createTextNode($countryVal);
						$country->appendChild($countryText);
						$author->appendChild($country);

						$email = $dom->createElement("email");
						$emailText = $dom->createTextNode($authInfo["Author".$authorIndex."_Email"]);
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
			$emailCount = 0;

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

				foreach ($cleanData as $key => $value) 
				{

					$key_arr = explode("_",$key);

					$issue = $dom->createElement("issue");
					$child = $issues->appendChild($issue);
					$child->setAttributeNode(new DOMAttr('current', 'false'));
					$child->setAttributeNode(new DOMAttr('published', 'true'));


					$issueTitle = $dom->createElement("title");
					$child = $issue->appendChild($issueTitle);
					$child->setAttributeNode(new DOMAttr('locale','en_US'));
					$issueTitleText = $dom->createTextNode($key_arr[1]." ".$key_arr[2]);
					$issueTitle->appendChild($issueTitleText);


					$issueVolume = $dom->createElement("volume");
					$issueVolText = $dom->createTextNode($key_arr[0]);
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
						Add the cover information
					*/

					$this->addCoverInfo($dom, $issue, "./".$key."/".$value["Cover"][Filename]);

					/*
						Some other information such as data published, open access
					*/

					$issueDate = $dom->createElement("date_published");
					//$issueYearText = $dom->createTextNode($key_arr[1]);
					$issueDateText = $dom->createTextNode("2014-09-18");
					$issueDate->appendChild($issueDateText);
					$issue->appendChild($issueDate);

					$issueAccess = $dom->createElement("open_access");
					//$issueYearText = $dom->createTextNode($key_arr[1]);
					//$issueYear->appendChild($issueYearText);
					$issue->appendChild($issueAccess);

					/*
						Add the front matter
					*/
					$this->AddFrontInfo($dom, $issue,"./".$key."/".$value["FRO"][Filename], $value["FRO"][Author1_Last], $value["FRO"][Author1_First], "frontmatter-000@ou.edu");

					

					// create the nodes for the articles in this issue:
					foreach ($value["content"] as $index => $articleInfo) 
					{
						$artType = $articleInfo["Type"];
						switch($articleInfo["Type"])
						{
							case "ART":
								if(!isset($artSection))
								{
									// create the section for articles:
									$artSection = $dom->createElement("section");
									$issue->appendChild($artSection);

									$titleArticles = $dom->createElement("title");
									$titleArticlesText = $dom->createTextNode("Articles");
									$titleArticles->appendChild($titleArticlesText);
									$child = $artSection->appendChild($titleArticles);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));

									$abbrev = $dom->createElement("abbrev");
									$abbrevText = $dom->createTextNode("ART");
									$abbrev->appendChild($abbrevText);
									$child = $artSection->appendChild($abbrev);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));
								}
								$section = $artSection;
								break;
							case "BKS":
								if(!isset($bksSection))
								{
									// create the section for articles:
									$bksSection = $dom->createElement("section");
									$issue->appendChild($bksSection);

									$titleArticles = $dom->createElement("title");
									$titleArticlesText = $dom->createTextNode("Book Reviews");
									$titleArticles->appendChild($titleArticlesText);
									$child = $bksSection->appendChild($titleArticles);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));

									$abbrev = $dom->createElement("abbrev");
									$abbrevText = $dom->createTextNode("BKS");
									$abbrev->appendChild($abbrevText);
									$child = $bksSection->appendChild($abbrev);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));
								}
								$section = $bksSection;
								break;
							case "MISC":
								if(!isset($miscSection))
								{
									// create the section for articles:
									$miscSection = $dom->createElement("section");
									$issue->appendChild($miscSection);

									$titleArticles = $dom->createElement("title");
									$titleArticlesText = $dom->createTextNode("Miscellaneous");
									$titleArticles->appendChild($titleArticlesText);
									$child = $miscSection->appendChild($titleArticles);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));

									$abbrev = $dom->createElement("abbrev");
									$abbrevText = $dom->createTextNode("MISC");
									$abbrev->appendChild($abbrevText);
									$child = $miscSection->appendChild($abbrev);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));
								}
								$section = $miscSection;
								break;
							case "INTRO":
								if(!isset($introSection))
								{
									// create the section for articles:
									$introsection = $dom->createElement("section");
									$issue->appendChild($introsection);

									$titleArticles = $dom->createElement("title");
									$titleArticlesText = $dom->createTextNode("Introduction");
									$titleArticles->appendChild($titleArticlesText);
									$child = $introsection->appendChild($titleArticles);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));

									$abbrev = $dom->createElement("abbrev");
									$abbrevText = $dom->createTextNode("INTRO");
									$abbrev->appendChild($abbrevText);
									$child = $introsection->appendChild($abbrev);
									$child->setAttributeNode(new DOMAttr('locale','en_US'));
								}
								$section = $introsection;
								break;
							default:
								echo "\nThe article type is wrong!\n";
								die;
								break;
						}

						$article = $dom->createElement("article");
						$child = $section->appendChild($article);
						$child->setAttributeNode(new DOMAttr('language','en'));
						$child->setAttributeNode(new DOMAttr('locale','en_US'));

						$articleTitle = $dom->createElement("title");
						$child = $article->appendChild($articleTitle);
						$child->setAttributeNode(new DOMAttr('locale','en_US'));
						$articleTitleText = $dom->createTextNode($articleInfo[Title]);
						$articleTitle->appendChild($articleTitleText);

						if(($artType == "ART" || $artType == "INTRO") && $articleInfo['Abstract'] != "")
						{
							$articleAbstract = $dom->createElement("Abstract");
							$child = $article->appendChild($articleAbstract);
							$child->setAttributeNode(new DOMAttr('locale','en_US'));
							$articleAbstractText = $dom->createTextNode($articleInfo['Abstract']);
							$articleAbstract->appendChild($articleAbstractText);
						}

						$articleIndex = $dom->createElement("indexing");
						$child = $article->appendChild($articleIndex);
						$child->setAttributeNode(new DOMAttr('locale','en_US'));

						$articleDiscipline = $dom->createElement("discipline");
						$child = $articleIndex->appendChild($articleDiscipline);
						$child->setAttributeNode(new DOMAttr('Locale','en_US'));
						$articleDisciplineText = $dom->createTextNode("Politics; United States; America");
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
						$child->setAttributeNode(new DOMAttr('src','./'.$key."/".$articleInfo[Filename]));
						$child->setAttributeNode(new DOMAttr('mime_type','application/pdf'));


						$this->addArticleAuthorInfo($dom, $article, $articleInfo, "tao-".$emailCount."@oouu.edu");
						$emailCount++;
					}

					unset($artSection);
					unset($miscSection);
					unset($bksSection);
					unset($introSection);
					
				}

			    /* get the xml printed */
			    $dom->save("./output-2011.xml");
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

					$affiliation = $dom->createElement("affiliation");
					$affiliationText = $dom->createTextNode($value["Affiliation"]);
					$affiliation->appendChild($affiliationText);
					$user->appendChild($affiliation);

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
					$dom->save("./output_users_11-21-2014.xml");
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
			$data = $this->data;
			foreach ($data as $key => $value) {

				if($value[Issue] == "" || $value[Volume] == "" || $value[Year] == "")
					continue;

				if(!isset($value[Filename]) || $value[Filename] == "")
					echo "The issue of ".$value[Issue]." and volume of ".$value[Volume]." in year ".$value[Year]." does not have file!\n";

				$issue = $value[Volume]."_".$value[Issue]."_".$value[Year]; 

				unset($value[Issue]);
				unset($value[Volume]);
				unset($value[Year]);

				switch($value[Type]){
					case "Cover":
						$cleanData[$issue]["Cover"] = $value;
						break;
					case "FRO":
						$cleanData[$issue]["FRO"] = $value;
						break;
					case "ART":
					case "BKS":
					case "MISC":
					case "INTRO":
						$cleanData[$issue]["content"][] = $value;
						break;
					// case "BKS":
					// 	if($value[Author1_First] != "" && $value[Author1_Last] != "")
					// 		$cleanData[$issue]["BKS"][] = $value;
					// 	break;
					// case "MISC":
					// 	$cleanData[$issue]["MISC"] = $value;
					// 	break;
					default:
						echo "The record type cannot be specified!\n";
						die;
						break;
				} 

				// if($value[Filename] != "" && $value[Volume] != ""  && $value[Year] != "" && $value[Issue] != "" 
				// 	&& $value[Author1_First] != "" && $value[Author1_Last] != ""){
					
				// 	unset($value[Issue]);
				// 	unset($value[Volume]);
				// 	unset($value[Year]);
				// 	$cleanData[$issue][] = $value;
				// }
			}//printInfo($cleanData);die;
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
		$ojs->setFiles("/Users/zhao0677/Projects/OJS-import/ARP_32_2011.xlsx");
		$ojs->getXmlFromArray("native.dtd");
	}
	else{
		$ojs->setFiles("/Users/zhao0677/Projects/OJS-import/APR_USERS_11-12-2014.xlsx");
		$ojs->getUserXmlFromArray("users.dtd");
	}


