<?php

$lang = array(
    'lang_name' => 'English',
    
    'myalbum' => 'My album',
	'title_install' => SOFT_NAME.' Installation Wizard',
	'agreement_yes' => 'Agree',
	'agreement_no' => 'Disagree',
	
	'install_locked' => 'Install already installed, if you sure you want to re-install, go to the server to delete <br /> '.str_replace(ROOTDIR, '', $lockfile),
	'error_quit_msg' => 'You have to solve the above problem, the installation can continue!',
	
	'click_to_back' => 'Click to return',
	'method_undefined' => 'Undefined method',
	'license' => '<div class="license"> <h1> English version of the License Agreement for English users </ h1> <p> © 2010-2012 Meiu Studio meiu.cn. All rights reserved. </p> <p> Thank you for choosing MeiuPic album management system. We hope that our efforts to provide you with an efficient image management, your support will be our biggest driving force for development. </p>
    <h3> I. License agreement right </ h3>
    <ol>
    <li> You can fully comply with the end user license agreement, based on the software used in this non-commercial use, without having to pay licensing fees for software copyright. </li>
    <li> Agreement you can within the constraints and restrictions change MeiuPic (if available) or interface style to suit your site requirements. </li>
    <li> You have to use this software to build a forum in full membership information, articles and related information of ownership, and independent take on legal obligations related to the article content. </li>
    <li> Obtain a commercial license, you can use this software for commercial purposes, while according to the type of license purchased in the period to determine the technical support, technical support, methods and technical support content, from the moment of purchase, technical support in the period has been designated by the manner specified range of technical support services. Business authorized users have the power to reflect and comment, with comments to be a primary consideration, but must be adopted no promise or guarantee. </li>
    </ol>

    <h3> II. Agreement of the constraints and limitations </ h3>
    <ol>
    <li> Before without a commercial license, the software may not be used for commercial purposes (including but not limited to corporate websites, business website, for commercial purpose or profit web site). Purchase of commercial license please visit http://www.meiu.cn reference instructions. </li>
    <li> Not associated with the Software or the commercial license for rent, sell, mortgage or grant sub-licenses. </li>
    <li> In any case, that is, no matter how the use, without modification or landscaping, changes to what extent, as long as the use MeiuPic whole or any part, without the written permission of MeiuPic page footer at the system under the name of beauty excellent album website (http://www.meiu.cn or http://www.meiupic.com) the link must be retained, not removed or modified. </li>
    <li> Prohibited MeiuPic the whole or any part of the basis for the development of any derivative version, modified version or third-party version for re-distribution. </li>
    <li> If you fail to comply with the terms of this Agreement, your license will be terminated, the licensee\'s rights will be withdrawn, and bear the corresponding legal responsibility. </li>
    </ol>

    <h3> III. LIMITED WARRANTY AND DISCLAIMER </ h3>
    <ol>
    <li> The software and accompanying documentation as does not provide any express or implied, or guarantee in the form of compensation provided. </li>
    <li> User voluntary use of this software, you must understand the risks of using this software, not yet purchased the product technical service, we do not promise to provide any form of technical support, use the warrant or assume any use of this software The problems related to responsibility. </li>
    <li> Meiu Studio meiu.cn not use this software to build the forum information in the article or responsibility. </li>
    </ol>

    <p> The MeiuPic end user license agreement, business license and details of technical services, provided exclusively by MeiuPic official website. Meiu Studio with and without prior notice, modify the license agreement and the power of service price list, the revised Agreement or change in the price list from the date of the new authorized users to take effect. </p>

    <p> Electronic form of a written license agreement as the two sides signed agreements, with full and equal legal effect. Once you start the installation MeiuPic, shall be deemed to fully understand and accept the terms of this Agreement, in the enjoyment of the powers conferred by these provisions at the same time, by the relevant constraints and limitations. Agreement beyond the permitted behavior, a direct violation of the license agreement and constitutes copyright infringement, we reserve the right to terminate the authorization, shall be ordered to stop the damage, and reserve the responsible authority. </p> </div>',
	
	'notset' => 'Unlimited',
	'writeable' => 'Writable',
	'unwriteable' => 'Unwritable',
	'nodir' => 'Not a directory',
	
	'step_env_title' => 'Start installation',
	'step_env_desc' => 'Check the installation environment and directory permissions',
	'php_version_too_low' => 'php version is too low',
	
	'old_step' => 'Previous',
	'new_step' => 'Next',
	
	'not_continue' => 'There\'s some errors, and can not continue. Correct it to continue.',
	
	'supportted' => 'Supported',
	'unsupportted' => 'Not supported',
	'project' => 'Project',
	'center_required' => 'Requirements',
	'center_best' => 'Recommended',
	'curr_server' => 'Current',
	'env_check' => 'Environmental inspection',
	'os' => 'OS',
	'php' => 'PHP version',
	'attachmentupload' => 'Attachment upload',
	'unlimit' => 'Unlimited',
	'version' => 'Version',
	'gdversion' => 'GD library',
	'noext' => 'No this extension',
	'allow' => 'Allow',
	'unix' => 'Unix',
	'diskspace' => 'Disk space',
	'priv_check' => 'Directories, file permissions',
	'func_depend' => 'Depended functions',
	'func_name' => 'Function',
	'check_result' => 'Result',
	'suggestion' => 'Suggestion',
	'advice_copy' => 'Change php.ini to open "copy" function.',
	'advice_file_get_contents' => 'This function requires allow_url_fopen in php.ini options open. ',
	'none' => 'None',
	
	'step1_file' => 'Directory files',
	'step1_need_status' => 'The required state',
	'step1_status' => 'The current state',
	
	'database' => 'Database',
	'0db' => 'No database supported',
	'1db' => 'Mysql',
	'2db' => 'Sqlite',
	'3db' => 'Mysql, Sqlite',
	
	'step_db_init_title' => 'Install the database',
	'step_db_init_desc' => 'Running the database installation',
	'sel_db_type' => 'Select the database type',
	'db_type' => 'Database',
	'db_type_comments' => 'Sqlite is not recommended for production environment',
	
	'tips_mysqldbinfo' => 'Database Info',
	'tips_mysqldbinfo_comment' => '',
	
	'tips_admininfo' => 'Adminitrator Info',
	'tips_admininfo_comment' => '',
	'username' => 'Account',
	'password' => 'Password',
	'password2' => 'Password again',
	'email' => 'Email',
	
	'password_comment' => 'Administrator password can not be empty.',
	
	'sqlite' => 'Use sqlite',
	'sqlite_check_label' => 'Sqlite Info',
	'sqlite_comment' => 'Sqlite is not recommended for production environment',
	
	'dbhost' => 'host',
	'dbuser' => 'Username',
	'dbport' => 'Port',
	'dbpw' => 'Password',
	'dbname' => 'Database',
	'tablepre' => 'Table prefix',
	'dbport_comment' => 'Normally set 3306',
	'dbhost_comment' => 'Normally set localhost',
	'tablepre_comment' => 'The same database system to run multiple albums, change it',
	
	'mysqldbinfo_dbhost_invalid' => 'Mysql host is invalid',
	'mysqldbinfo_dbname_invalid' => 'Mysql dbname is invalid',
	'mysqldbinfo_dbuser_invalid' => 'Mysql username is invalid',
	'mysqldbinfo_dbpw_invalid' => 'Mysql password is invalid',
	'mysqldbinfo_adminemail_invalid' => 'Admin email is invalid',
	'mysqldbinfo_tablepre_invalid' => 'Table prefix is invalid',
	'admininfo_username_invalid' => 'Administrator account is invalid',
	'admininfo_email_invalid' => 'Admin email is invalid',
	'admininfo_password_invalid' => 'Administrator password can not be empty',
	'admininfo_password2_invalid' => 'The two passwords do not match, pls. check it',
	
	'admininfo_invalid' => 'Administrator information is not complete',
	'dbname_invalid' => 'Database name can not be empty',
	'tablepre_invalid' => 'Table prefix is invalid',
	'admin_username_invalid' => 'Administrator account is invalid',
	'admin_password_invalid' => 'Administrator account is invalid',
	'admin_email_invalid' => 'Admin email is invalid',
	'admin_invalid' => 'Administrator information is not complete',
	
	'tips_siteinfo' => 'Website information',
	
	'siteurl' => 'Website URL',
	'sitename' => 'Website name',
	'siteurl_comment' => 'Pls. leave the "/" at the end of URL',
	
	'database_errno_2003' => 'Can not connect to the database',
	'database_errno_1044' => 'Can not create a new database',
	'database_errno_1045' => 'Can not connect to the database, username or password error',
	'database_connect_error' => 'Can not connect to the database',

	'install_in_processed' => 'Installing...',
	'create_table' => 'Create table ',
	'succeed' => 'successfully',
	'install_data_sql' => 'Install the initialization data',
	
	'undefine_func' => 'Can not find mysql extension',
	
	'clear_dir' => 'Clear directory ',
	'create_admin_account' => 'Create admin account',
	'failed' => 'Failed',
	'update_user_setting' =>'Update user setting',
	'installed_complete' => 'Install complete ...',
	
	'forceinstall' => 'Force install',
	'mysqldbinfo_forceinstall_invalid' => 'There\'s some tables with the save prefix. You could change the table prefix to avoid conflict. But you can choose "Force install" to delete the old data and continue to install.',
    'forceinstall_check_label' => 'I want to delete all data !!!',
    
    'tips_sqlite' => 'Sqlite Information',
    'sqlite_forceinstall_invalid' => 'These\'s a sqlite database in the destination directory. You could change the table prefix to avoid conflict. But you can choose "Force install" to delete the old data and continue to install.',
    
    'step_complete_title' => 'Successful installation',
    'step_complete_desc' => '&nbsp;',
    'install_succeed' => 'Congratulations on your successful installation',
    'auto_redirect' => 'The program will automatically jump',
    
    
    'file_not_exists'        =>   'File %s not exists!',
    'db_config_error'        =>   'Database configuration error,please review the configuration file!',
    'sqlite_not_exists'      =>   'Sqlite doesn\'t exist!',
    'miss_dbname'            =>   'Please set up the database name!',
    'connect_mysql'          =>    'Connection to Mysql (%s,%s) is failed!',
    'can_not_use_db'         =>    'Can\'t use the database %s',
    
    'install_default_theme'  =>     'Install the default themes',
    'miss_default_theme'     =>    'Miss the default theme',
    'install_default_plugins' => 'Install the default plugins',


    'home'              =>  'Home',
    'tags'              =>  'Tags',
    'category'          =>  'Category',
    'share_title'       =>  'Share a great photo: {name}',
    'site_keywords'     =>  'album,gallery,share',
    'site_desc'         =>  'Built with MeiuPic. MeiuPic is a photo gallery software for the web. It is free and opensource.',
);