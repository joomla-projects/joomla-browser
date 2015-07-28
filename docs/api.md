


####  doAdministratorLogin() 
Function to Do Admin Login In Joomla!

####  doFrontEndLogin() 
Function to Do frontend Login in Joomla!

####  installJoomla() 
Installs Joomla

####  installJoomlaRemovingInstallationFolder() 
Install Joomla removing the Installation folder at the end of the execution

####  installJoomlaMultilingualSite($languages = null) 
Installs Joomla with Multilingual Feature active

 * `param`   array  $languages  Array containing the language names to be installed

 * `example:` $I->installJoomlaMultilingualSite(['Spanish', 'French']);

####  setErrorReportingToDevelopment() 
Sets in Adminitrator->Global Configuration the Error reporting to Development

@note: doAdminLogin() before

####  installExtensionFromDirectory($path, $type = null) 
Installs a Extension in Joomla that is located in a folder inside the server

 * `param`   String  $path  Path for the Extension
 * `param`   string  $type  Type of Extension

@note: doAdminLogin() before

####  installExtensionFromUrl($url, $type = null) 
Installs a Extension in Joomla that is located in a url

 * `param`   String  $url   Url address to the .zip file
 * `param`   string  $type  Type of Extension

@note: doAdminLogin() before

####  checkForPhpNoticesOrWarnings($page = null) 
Function to check for PHP Notices or Warnings

 * `param string` $page Optional, if not given checks will be done in the current page

@note: doAdminLogin() before

####  selectOptionInRadioField($label, $option) 
Selects an option in a Joomla Radio Field based on its label

@return void

####  selectOptionInChosen($label, $option) 
Selects an option in a Chosen Selector based on its label

@return void

####  selectMultipleOptionsInChosen($label, $options) 
Selects one or more options in a Chosen Multiple Select based on its label.

 * `param`   string  $label    Label of the select field
 * `param`   array   $options  Array of options to be selected

@return void

####  doAdministratorLogout() 
Function to Logout from Administrator Panel in Joomla.

@return void

####  enablePlugin($pluginName) 
Function to Enable a Plugin.

 * `param`   string  $pluginName  Name of the Plugin

@return void

####  uninstallExtension($extensionName) 
Uninstall Extension based on a name

 * `param`   string  $extensionName  Is important to use a specific

####  searchForItem($name = null, $options = null) 
Function to Search For an Item in Joomla! Administrator Lists views

 * `param`   String  $name     Name of the Item which we need to Search. If null it cleans the searchbox
 * `param`   array   $options  Optional Array containing the locators of the search buttons

@return void

####  checkExistenceOf($name) 
Function to Check of the Item Exist in Search Results in Administrator List.

note: on long lists of items the item that your are looking for may not appear in the first page. We recommend
the usage of searchForItem method before using the current method.

 * `param`   String  $name  Name of the Item which we are Searching For

@return void

####  checkAllResults() 
Function to select all the item in the Search results in Administrator List

Note: We recommend use of checkAllResults function only after searchForItem to be sure you are selecting only the desired result set

@return void

####  installLanguage($languageName) 
Function to install a language through the interface

 * `param string` $languageName Name of the language you want to install

@return void


