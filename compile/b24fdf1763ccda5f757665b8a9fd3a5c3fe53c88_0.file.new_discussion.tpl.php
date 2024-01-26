<?php
/* Smarty version 3.1.33, created on 2023-12-02 17:25:36
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/discussions/notifications/email/new_discussion.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656b6890aa3357_32221389',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b24fdf1763ccda5f757665b8a9fd3a5c3fe53c88' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/discussions/notifications/email/new_discussion.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656b6890aa3357_32221389 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/function.notification_attachments_table.php','function'=>'smarty_function_notification_attachments_table',),2=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/function.notification_inspector.php','function'=>'smarty_function_notification_inspector',),));
?>
[<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getProject()->getName(),$_smarty_tpl);?>
] <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Discussion ':name' has been Created<?php $_block_repeat=false;
echo smarty_block_lang(array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
================================================================================
<!-- Message Body -->
<h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('author'=>$_smarty_tpl->tpl_vars['context']->value->getCreatedBy()->getDisplayName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('author'=>$_smarty_tpl->tpl_vars['context']->value->getCreatedBy()->getDisplayName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:author invited you to the discussion:<?php $_block_repeat=false;
echo smarty_block_lang(array('author'=>$_smarty_tpl->tpl_vars['context']->value->getCreatedBy()->getDisplayName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
    <br/>
    <a href="<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getViewUrl(),$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['context']->value->getName(),$_smarty_tpl);?>
</a>
</h1>

<!-- Description -->
<?php echo $_smarty_tpl->tpl_vars['context']->value->getFormattedBody('email');?>

<?php echo smarty_function_notification_attachments_table(array('object'=>$_smarty_tpl->tpl_vars['context']->value,'recipient'=>$_smarty_tpl->tpl_vars['recipient']->value),$_smarty_tpl);?>


<!-- Metadata -->
<?php echo smarty_function_notification_inspector(array('context'=>$_smarty_tpl->tpl_vars['context']->value,'recipient'=>$_smarty_tpl->tpl_vars['recipient']->value,'link_style'=>'color: #999999; text-decoration: none;'),$_smarty_tpl);
}
}
