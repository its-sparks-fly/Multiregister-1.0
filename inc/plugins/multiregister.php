<?php

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook('usercp_start', 'multiregister_usercp');
$plugins->add_hook('usercp_menu', 'multiregister_nav', 40);

function multiregister_info()
{
	return array(
		"name"			=> "Multiregister",
		"description"	=> "Registrierung von Mehrcharakteren über das UserCP inkl. automatischer Verknüpfung durch Accountswitcher",
		"website"		=> "https://github.com/its-sparks-fly",
		"author"		=> "sparks fly",
		"authorsite"	=> "https://github.com/its-sparks-fly",
		"version"		=> "1.0",
		"compatibility" => "18*"
	);
}

function multiregister_install()
{
	global $db, $mybb;

	$setting_group = array(
	    'name' => 'multiregister',
	    'title' => 'Multiregister',
	    'description' => 'Einstellungen für das Multiregister-Plugin.',
	    'disporder' => 1,
	    'isdefault' => 0
	);
	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
		// A yes/no boolean box
		'multiregister_as' => array(
				'title' => 'Mit dem Accountswitcher verbinden?',
				'description' => 'Sollen neu erstellte Charaktere automatisch mit dem entsprechenden Hauptaccount verbunden werden?',
				'optionscode' => 'yesno',
				'value' => 0,
				'disporder' => 1
		),
	  'multiregister_usergroup' => array(
      	'title' => 'Benutzergruppe für neu erstellte Accounts',
	      'description' => 'In welcher Nutzergruppe sollen neu erstellte Accounts landen?',
	      'optionscode' => 'text',
	      'value' => '',
       'disporder' => 2
	  ),
	);

	foreach($setting_array as $name => $setting)
	{
	    $setting['name'] = $name;
	    $setting['gid'] = $gid;

	    $db->insert_query('settings', $setting);
	}
	rebuild_settings();

	$insert_array = array(
		'title'		=> 'usercp_multiregister',
		'template'	=> $db->escape_string('<html>
<head>
<title>{$lang->user_cp}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
{$usercpnav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="{$colspan}"><strong>{$lang->usercp_nav_multiregister}</strong></td>
</tr>
<tr>
<form action="usercp.php" method="post" id="registration_form"><input type="text" style="visibility: hidden;" value="" name="regcheck1" /><input type="text" style="visibility: hidden;" value="true" name="regcheck2" />
{$regerrors}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->registration}</strong></td>
</tr>
{$masteraccount}
<tr>
<td width="100%" class="trow1" valign="top">
<fieldset class="trow2">
<legend><strong>{$lang->account_details}</strong></legend>
<table cellspacing="0" cellpadding="{$theme[\'tablespace\']}" width="100%">
<tr>
<td colspan="2"><span class="smalltext"><label for="username">{$lang->username}</label></span></td>
</tr>
<tr>
<td colspan="2"><input type="text" class="textbox" name="username" id="username" style="width: 100%" value="{$username}" /></td>
</tr>
{$passboxes}
<tr>
	<td colspan="2" style="display: none;" id="email_status">&nbsp;</td>
</tr>
</table>
</fieldset>
{$requiredfields}
{$customfields}
</td>
</tr>
</table>
<br />
<div align="center">
<input type="hidden" name="regtime" value="{$time}" />
<input type="hidden" name="step" value="registration" />
<input type="hidden" name="action" value="do_multiregister" />
<input type="submit" class="button" name="regsubmit" value="{$lang->send_character}" />
</div>
</form>
</tr>
</table>
{$footer}
</body>
</html>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'usercp_multiregister_master',
		'template'	=> $db->escape_string('<tr>
	<td width="100%" class="trow1 smalltext" valign="top">
		<fieldset class="trow2">
			<center>{$lang->masteraccount} <b>{$mastername}</b></center>
		</fieldset>
	</td>
</tr>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'usercp_nav_multiregister',
		'template'	=> $db->escape_string('<tr><td class="trow1 smalltext"><a href="usercp.php?action=multiregister" class="usercp_nav_item usercp_nav_usergroups">{$lang->usercp_nav_multiregister}</a></td></tr>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

}

function multiregister_is_installed()
{
	global $mybb;

	if(isset($mybb->settings['multiregister_usergroup']))
	{
	    return true;
	}

	return false;
}

function multiregister_uninstall()
{
	global $db;

	$db->delete_query('settings', "name LIKE 'multiregister_%'");
	$db->delete_query('settinggroups', "name = 'multiregister'");
	rebuild_settings();
	$db->delete_query('templates', "title LIKE '%multiregister%'");
}

function multiregister_usercp() {
	global $mybb, $db, $cache, $plugins, $templates, $theme, $lang, $header, $headerinclude, $footer, $usercpnav, $customfields, $requiredfields;
	$lang->load("member");
	$lang->load("multiregister");

	if($mybb->input['action'] == "do_multiregister" && $mybb->request_method == "post")
	{
		if($mybb->settings['regtype'] == "verify" || $mybb->settings['regtype'] == "admin" || $mybb->settings['regtype'] == "both" || $mybb->get_input('coppa', MyBB::INPUT_INT) == 1)
		{
			$usergroup = 5;
		}
		else
		{
			$usergroup = 2;
		}

		// Set up user handler.
		require_once MYBB_ROOT."inc/datahandlers/user.php";
		$userhandler = new UserDataHandler("insert");

		$coppauser = 0;
		if(isset($mybb->cookies['coppauser']))
		{
			$coppauser = (int)$mybb->cookies['coppauser'];
		}

		// Set the data for the new user.
		$user = array(
			"username" => $mybb->get_input('username'),
			"password" => $mybb->get_input('password'),
			"password2" => $mybb->get_input('password2'),
			"email" => $mybb->user['email'],
			"email2" => $mybb->user['email'],
			"usergroup" => $mybb->settings['multiregister_usergroup'],
			"referrer" => $mybb->user['referrername'],
			"timezone" => $mybb->user['timezoneoffset'],
			"language" => $mybb->user['language'],
			"profile_fields" => $mybb->get_input('profile_fields', MyBB::INPUT_ARRAY),
			"regip" => $session->packedip,
			"coppa_user" => $coppauser,
			"regcheck1" => $mybb->get_input('regcheck1'),
			"regcheck2" => $mybb->get_input('regcheck2'),
			"registration" => true
		);

		$user['options'] = array(
			"allownotices" => $mybb->user['allownotices'],
			"hideemail" => $mybb->user['hideemail'],
			"subscriptionmethod" => $mybb->user['subscriptionmethod'],
			"receivepms" => $mybb->user['receivepms'],
			"pmnotice" => $mybb->user['pmnotice'],
			"pmnotify" => $mybb->user['pmnotify'],
			"invisible" => $mybb->user['invisible'],
			"dstcorrection" => $mybb->user['dstcorrection']
		);

		$userhandler->set_data($user);

		$errors = "";

		if(!$userhandler->validate_user())
		{
			$errors = $userhandler->get_friendly_errors();
		}

		if(is_array($errors))
		{
			$username = htmlspecialchars_uni($mybb->get_input('username'));
			$regerrors = inline_error($errors);
			$mybb->input['action'] = "multiregister";
			$fromreg = 1;
		}
		else
		{
			$user_info = $userhandler->insert_user();
			$lang->redirect_registered = $lang->sprintf($lang->redirect_registered, $mybb->settings['bbname'], htmlspecialchars_uni($user_info['username']));
			if($mybb->settings['multiregister_as'] == "1") {
				if($mybb->user['as_uid'] != "0") {
					$masteruid = $mybb->user['as_uid'];
				}
				else { $masteruid = $mybb->user['uid']; }
				$new_record = array(
					"as_uid" => (int)$masteruid
				);
			$db->update_query("users", $new_record, "uid = '$user_info[uid]'");
			}
			if($mybb->settings['multiregister_as'] == "1") {
				require_once MYBB_ROOT.'/inc/plugins/accountswitcher/class_accountswitcher.php';
	    	$eas = new AccountSwitcher($mybb, $db, $cache, $templates);
	    	$eas->update_accountswitcher_cache();
			}
			redirect("index.php", $lang->redirect_registered);
		}
	}
	if($mybb->input['action'] == "multiregister")
	{
			$plugins->run_hooks("member_register_start");

			$validator_extra = '';

			$mybb->input['profile_fields'] = $mybb->get_input('profile_fields', MyBB::INPUT_ARRAY);
			// Custom profile fields baby!
			$altbg = "trow1";
			$requiredfields = $customfields = '';

			if($mybb->settings['regtype'] == "verify" || $mybb->settings['regtype'] == "admin" || $mybb->settings['regtype'] == "both" || $mybb->get_input('coppa', MyBB::INPUT_INT) == 1)
			{
				$usergroup = 5;
			}
			else
			{
				$usergroup = 2;
			}

			$pfcache = $cache->read('profilefields');

			if(is_array($pfcache))
			{
				foreach($pfcache as $profilefield)
				{
					if($profilefield['required'] != 1 && $profilefield['registration'] != 1 || !is_member($profilefield['editableby'], array('usergroup' => $mybb->user['usergroup'], 'additionalgroups' => $usergroup)))
					{
						continue;
					}

					$code = $select = $val = $options = $expoptions = $useropts = $seloptions = '';
					$profilefield['type'] = htmlspecialchars_uni($profilefield['type']);
					$thing = explode("\n", $profilefield['type'], "2");
					$type = trim($thing[0]);
					$options = $thing[1];
					$select = '';
					$field = "fid{$profilefield['fid']}";
					$profilefield['description'] = htmlspecialchars_uni($profilefield['description']);
					$profilefield['name'] = htmlspecialchars_uni($profilefield['name']);
					if($errors && isset($mybb->input['profile_fields'][$field]))
					{
						$userfield = $mybb->input['profile_fields'][$field];
					}
					else
					{
						$userfield = '';
					}
					if($type == "multiselect")
					{
						if($errors)
						{
							$useropts = $userfield;
						}
						else
						{
							$useropts = explode("\n", $userfield);
						}
						if(is_array($useropts))
						{
							foreach($useropts as $key => $val)
							{
								$seloptions[$val] = $val;
							}
						}
						$expoptions = explode("\n", $options);
						if(is_array($expoptions))
						{
							foreach($expoptions as $key => $val)
							{
								$val = trim($val);
								$val = str_replace("\n", "\\n", $val);

								$sel = "";
								if(isset($seloptions[$val]) && $val == $seloptions[$val])
								{
									$sel = ' selected="selected"';
								}

								eval("\$select .= \"".$templates->get("usercp_profile_profilefields_select_option")."\";");
							}
							if(!$profilefield['length'])
							{
								$profilefield['length'] = 3;
							}

							eval("\$code = \"".$templates->get("usercp_profile_profilefields_multiselect")."\";");
						}
					}
					elseif($type == "select")
					{
						$expoptions = explode("\n", $options);
						if(is_array($expoptions))
						{
							foreach($expoptions as $key => $val)
							{
								$val = trim($val);
								$val = str_replace("\n", "\\n", $val);
								$sel = "";
								if($val == $userfield)
								{
									$sel = ' selected="selected"';
								}

								eval("\$select .= \"".$templates->get("usercp_profile_profilefields_select_option")."\";");
							}
							if(!$profilefield['length'])
							{
								$profilefield['length'] = 1;
							}

							eval("\$code = \"".$templates->get("usercp_profile_profilefields_select")."\";");
						}
					}
					elseif($type == "radio")
					{
						$expoptions = explode("\n", $options);
						if(is_array($expoptions))
						{
							foreach($expoptions as $key => $val)
							{
								$checked = "";
								if($val == $userfield)
								{
									$checked = 'checked="checked"';
								}

								eval("\$code .= \"".$templates->get("usercp_profile_profilefields_radio")."\";");
							}
						}
					}
					elseif($type == "checkbox")
					{
						if($errors)
						{
							$useropts = $userfield;
						}
						else
						{
							$useropts = explode("\n", $userfield);
						}
						if(is_array($useropts))
						{
							foreach($useropts as $key => $val)
							{
								$seloptions[$val] = $val;
							}
						}
						$expoptions = explode("\n", $options);
						if(is_array($expoptions))
						{
							foreach($expoptions as $key => $val)
							{
								$checked = "";
								if(isset($seloptions[$val]) && $val == $seloptions[$val])
								{
									$checked = 'checked="checked"';
								}

								eval("\$code .= \"".$templates->get("usercp_profile_profilefields_checkbox")."\";");
							}
						}
					}
					elseif($type == "textarea")
					{
						$value = htmlspecialchars_uni($userfield);
						eval("\$code = \"".$templates->get("usercp_profile_profilefields_textarea")."\";");
					}
					else
					{
						$value = htmlspecialchars_uni($userfield);
						$maxlength = "";
						if($profilefield['maxlength'] > 0)
						{
							$maxlength = " maxlength=\"{$profilefield['maxlength']}\"";
						}

						eval("\$code = \"".$templates->get("usercp_profile_profilefields_text")."\";");
					}

					if($profilefield['required'] == 1)
					{
						// JS validator extra, choose correct selectors for everything except single select which always has value
						if($type != 'select')
						{
							if($type == "textarea")
							{
								$inp_selector = "$('textarea[name=\"profile_fields[{$field}]\"]')";
							}
							elseif($type == "multiselect")
							{
								$inp_selector = "$('select[name=\"profile_fields[{$field}][]\"]')";
							}
							elseif($type == "checkbox")
							{
								$inp_selector = "$('input[name=\"profile_fields[{$field}][]\"]')";
							}
							else
							{
								$inp_selector = "$('input[name=\"profile_fields[{$field}]\"]')";
							}

							$validator_extra .= "
							{$inp_selector}.rules('add', {
								required: true,
								messages: {
									required: '{$lang->js_validator_not_empty}'
								}
							});\n";
						}

						eval("\$requiredfields .= \"".$templates->get("member_register_customfield")."\";");
					}
					else
					{
						eval("\$customfields .= \"".$templates->get("member_register_customfield")."\";");
					}
				}

				if($requiredfields)
				{
					eval("\$requiredfields = \"".$templates->get("member_register_requiredfields")."\";");
				}

				if($customfields)
				{
					eval("\$customfields = \"".$templates->get("member_register_additionalfields")."\";");
				}
			}

			if($mybb->settings['regtype'] != "randompass")
			{
				// JS validator extra
				$lang->js_validator_password_length = $lang->sprintf($lang->js_validator_password_length, $mybb->settings['minpasswordlength']);

				// See if the board has "require complex passwords" enabled.
				if($mybb->settings['requirecomplexpasswords'] == 1)
				{
					$lang->password = $lang->complex_password = $lang->sprintf($lang->complex_password, $mybb->settings['minpasswordlength']);

					$validator_extra .= "
					$('#password').rules('add', {
						required: true,
						minlength: {$mybb->settings['minpasswordlength']},
						remote:{
							url: 'xmlhttp.php?action=complex_password',
							type: 'post',
							dataType: 'json',
							data:
							{
								my_post_key: my_post_key
							},
						},
						messages: {
							minlength: '{$lang->js_validator_password_length}',
							required: '{$lang->js_validator_password_length}',
							remote: '{$lang->js_validator_no_image_text}'
						}
					});\n";
				}
				else
				{
					$validator_extra .= "
					$('#password').rules('add', {
						required: true,
						minlength: {$mybb->settings['minpasswordlength']},
						messages: {
							minlength: '{$lang->js_validator_password_length}',
							required: '{$lang->js_validator_password_length}'
						}
					});\n";
				}

				$validator_extra .= "
					$('#password2').rules('add', {
						required: true,
						minlength: {$mybb->settings['minpasswordlength']},
						equalTo: '#password',
						messages: {
							minlength: '{$lang->js_validator_password_length}',
							required: '{$lang->js_validator_password_length}',
							equalTo: '{$lang->js_validator_password_matches}'
						}
					});\n";

				eval("\$passboxes = \"".$templates->get("member_register_password")."\";");
			}

			// JS validator extra
			if($mybb->settings['maxnamelength'] > 0 && $mybb->settings['minnamelength'] > 0)
			{
				$lang->js_validator_username_length = $lang->sprintf($lang->js_validator_username_length, $mybb->settings['minnamelength'], $mybb->settings['maxnamelength']);
			}

			// Set the time so we can find automated signups
			$time = TIME_NOW;
			if($mybb->settings['multiregister_as'] == "1") {
				$mastername = $db->fetch_field($db->query("SELECT username FROM mybb_users WHERE uid = '{$mybb->user['as_uid']}'"), "username");
				if(empty($mybb->user['as_uid'])) {
					$mastername = $mybb->user['username'];
				}
				eval("\$masteraccount = \"".$templates->get("usercp_multiregister_master")."\";");
			}
			eval("\$page= \"".$templates->get("usercp_multiregister")."\";");
			output_page($page);
		}
	}

function multiregister_nav() {
	global $mybb, $templates, $lang, $usercpmenu;
	$lang->load("multiregister");
	eval("\$usercpmenu .= \"".$templates->get("usercp_nav_multiregister")."\";");
}
