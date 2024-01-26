<?php
/* Smarty version 3.1.33, created on 2023-12-02 17:15:11
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/system/notifications/email/new_project.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656b661fea15a9_21989225',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '35655152bc0ee61c897887743580977f080be96f' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/system/notifications/email/new_project.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656b661fea15a9_21989225 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/function.notification_logo.php','function'=>'smarty_function_notification_logo',),));
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('project_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('project_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>You've been invited to join :project_name<?php $_block_repeat=false;
echo smarty_block_lang(array('project_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
================================================================================
<?php echo smarty_function_notification_logo(array(),$_smarty_tpl);?>


<!-- Message Body -->
<h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('invited_by'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'project_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('invited_by'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'project_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:invited_by added you to the project ":project_name"<?php $_block_repeat=false;
echo smarty_block_lang(array('invited_by'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'project_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
</h1>

<p><a href="<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getViewUrl(),$_smarty_tpl);?>
"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Click here to join the project<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></a></p>

<p><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>New to ActiveCollab? <a href="https://help.activecollab.com/">Visit this page</a> to find out more.<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></p>
<?php }
}
