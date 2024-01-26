<?php
/* Smarty version 3.1.33, created on 2023-12-02 17:58:04
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/system/notifications/email/invitation.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656b702cb1e4f6_84217862',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1b34aafd42c7a283c24755b166dd398cd3164fdd' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/system/notifications/email/invitation.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656b702cb1e4f6_84217862 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/function.notification_logo.php','function'=>'smarty_function_notification_logo',),));
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>You've been invited to join<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
================================================================================
<?php echo smarty_function_notification_logo(array(),$_smarty_tpl);?>


<!-- Message Body -->
<h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
<?php if ($_smarty_tpl->tpl_vars['invited_to']->value instanceof Project) {?>
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('invited_by'=>$_smarty_tpl->tpl_vars['invited_by']->value->getDisplayName(),'owner_company'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'project_name'=>$_smarty_tpl->tpl_vars['invited_to']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('invited_by'=>$_smarty_tpl->tpl_vars['invited_by']->value->getDisplayName(),'owner_company'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'project_name'=>$_smarty_tpl->tpl_vars['invited_to']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:invited_by from :owner_company has invited you to ActiveCollab to work together on the :project_name project.<?php $_block_repeat=false;
echo smarty_block_lang(array('invited_by'=>$_smarty_tpl->tpl_vars['invited_by']->value->getDisplayName(),'owner_company'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'project_name'=>$_smarty_tpl->tpl_vars['invited_to']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
} else { ?>
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('invited_by'=>$_smarty_tpl->tpl_vars['invited_by']->value->getDisplayName(),'owner_company'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('invited_by'=>$_smarty_tpl->tpl_vars['invited_by']->value->getDisplayName(),'owner_company'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:invited_by from :owner_company has invited you to ActiveCollab to work together on projects.<?php $_block_repeat=false;
echo smarty_block_lang(array('invited_by'=>$_smarty_tpl->tpl_vars['invited_by']->value->getDisplayName(),'owner_company'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
}?>
</h1>

<p><a href="<?php echo clean($_smarty_tpl->tpl_vars['invitation']->value->getAcceptUrl(),$_smarty_tpl);?>
"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Click here to log in<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></a></p>

<p style="font-size: 14px; color: #A9A9A9; text-decoration: none;"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('invited_by_email'=>$_smarty_tpl->tpl_vars['sender']->value->getEmail(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('invited_by_email'=>$_smarty_tpl->tpl_vars['sender']->value->getEmail(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>ActiveCollab is an online project management tool. If you're not sure why you got this email, please contact <a href="mailto::invited_by_email">:invited_by_email</a>.<?php $_block_repeat=false;
echo smarty_block_lang(array('invited_by_email'=>$_smarty_tpl->tpl_vars['sender']->value->getEmail(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></p>
<?php }
}
