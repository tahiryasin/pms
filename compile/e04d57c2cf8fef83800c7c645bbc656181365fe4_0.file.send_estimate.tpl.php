<?php
/* Smarty version 3.1.33, created on 2023-12-02 12:03:16
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/invoicing/notifications/email/send_estimate.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656b1d04026763_37207270',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'e04d57c2cf8fef83800c7c645bbc656181365fe4' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/invoicing/notifications/email/send_estimate.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656b1d04026763_37207270 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/modifier.money.php','function'=>'smarty_modifier_money',),2=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/email/helpers/modifier.notification_recipients.php','function'=>'smarty_modifier_notification_recipients',),));
if ($_smarty_tpl->tpl_vars['custom_subject']->value) {?>
    <?php echo $_smarty_tpl->tpl_vars['custom_subject']->value;?>

<?php } else { ?>
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Estimate ':name' has been Sent<?php $_block_repeat=false;
echo smarty_block_lang(array('name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
}?>
================================================================================
<!-- Message Body -->
<h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('estimate_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('estimate_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Estimate for :estimate_name from :company_name.<?php $_block_repeat=false;
echo smarty_block_lang(array('estimate_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
    <br/>
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Amount<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>: <?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['context']->value->getTotal(),$_smarty_tpl->tpl_vars['context']->value->getCurrency(),$_smarty_tpl->tpl_vars['language']->value,true,true),$_smarty_tpl);?>

</h1>

<?php if ($_smarty_tpl->tpl_vars['custom_message']->value) {?>
    <p><?php echo nl2br(htmlspecialchars($_smarty_tpl->tpl_vars['custom_message']->value, ENT_QUOTES, 'UTF-8', true));?>
</p>
<?php }?>

<!-- Metadata -->
<div class="metadata" style="color: #999999; font-size: 14px; line-height: 21px;">
    <p><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('sender_name'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('sender_name'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Sent by :sender_name from :company_name to<?php $_block_repeat=false;
echo smarty_block_lang(array('sender_name'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?> <?php echo smarty_modifier_notification_recipients($_smarty_tpl->tpl_vars['estimate_recipients']->value,$_smarty_tpl->tpl_vars['sender']->value,'color: #999999; text-decoration: none;',$_smarty_tpl->tpl_vars['language']->value);?>
</p>
</div>
<?php }
}
