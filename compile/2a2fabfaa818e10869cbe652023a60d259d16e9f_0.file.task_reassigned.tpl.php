<?php
/* Smarty version 3.1.33, created on 2023-12-02 17:17:34
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/tasks/notifications/email/task_reassigned.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656b66ae5f30c1_71942820',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2a2fabfaa818e10869cbe652023a60d259d16e9f' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/tasks/notifications/email/task_reassigned.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656b66ae5f30c1_71942820 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/function.notification_attachments_table.php','function'=>'smarty_function_notification_attachments_table',),2=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/function.notification_inspector.php','function'=>'smarty_function_notification_inspector',),));
?>
[<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getProject()->getName(),$_smarty_tpl);?>
] <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Task ':name' has been Assigned to You<?php $_block_repeat=false;
echo smarty_block_lang(array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
================================================================================
<!-- Message Body -->
<h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('author'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('author'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:author assigned you the task:<?php $_block_repeat=false;
echo smarty_block_lang(array('author'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?> &#10065; <br/>
    <a href="<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getViewUrl(),$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['context']->value->getName(),$_smarty_tpl);?>
</a>
    <?php if ($_smarty_tpl->tpl_vars['context']->value->getDueOn()) {?>
        <br/>
        <?php if ($_smarty_tpl->tpl_vars['context']->value->getDueOn()->getTimeStamp() <= strtotime('today')) {?>
            <?php $_smarty_tpl->_assignInScope('color', '#ff0000');?>
        <?php } else { ?>
            <?php $_smarty_tpl->_assignInScope('color', '#000000');?>
        <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['context']->value->getStartOn() && !$_smarty_tpl->tpl_vars['context']->value->getDueOn()->isSameDay($_smarty_tpl->tpl_vars['context']->value->getStartOn())) {?>
            <span style="color: <?php echo clean($_smarty_tpl->tpl_vars['color']->value,$_smarty_tpl);?>
; font-weight: normal;">
                <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('due_on'=>$_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'start_on'=>$_smarty_tpl->tpl_vars['context']->value->getStartOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('due_on'=>$_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'start_on'=>$_smarty_tpl->tpl_vars['context']->value->getStartOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:start_on &mdash; :due_on<?php $_block_repeat=false;
echo smarty_block_lang(array('due_on'=>$_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'start_on'=>$_smarty_tpl->tpl_vars['context']->value->getStartOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
            </span>
        <?php } else { ?>
            <span style="color: <?php echo clean($_smarty_tpl->tpl_vars['color']->value,$_smarty_tpl);?>
; font-weight: normal;">
                <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('due_on'=>$_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('due_on'=>$_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Due on :due_on<?php $_block_repeat=false;
echo smarty_block_lang(array('due_on'=>$_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
            </span>
        <?php }?>
    <?php }?>
</h1>

<!-- Description -->
<?php echo $_smarty_tpl->tpl_vars['context']->value->getFormattedBody('email');?>

<?php echo smarty_function_notification_attachments_table(array('object'=>$_smarty_tpl->tpl_vars['context']->value,'recipient'=>$_smarty_tpl->tpl_vars['recipient']->value),$_smarty_tpl);?>


<!-- Metadata -->
<?php echo smarty_function_notification_inspector(array('context'=>$_smarty_tpl->tpl_vars['context']->value,'recipient'=>$_smarty_tpl->tpl_vars['recipient']->value,'link_style'=>'color: #999999; text-decoration: none;'),$_smarty_tpl);
}
}
