<?php
/* Smarty version 3.1.33, created on 2023-12-09 11:15:43
  from 'D:\Work\projects\nr10\projects\activecollab\6.2.169\modules\invoicing\notifications\email\estimate_updated.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_65744c5fb4e812_82177937',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c2476cd71a1a5fed8a184f6a9605144ef1eef53d' => 
    array (
      0 => 'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\modules\\invoicing\\notifications\\email\\estimate_updated.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_65744c5fb4e812_82177937 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\environment\\helpers\\block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\environment\\helpers\\modifier.money.php','function'=>'smarty_modifier_money',),2=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\email\\helpers\\modifier.notification_recipients.php','function'=>'smarty_modifier_notification_recipients',),));
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Estimate updated<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
================================================================================
<!-- Message Body -->
<h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('estimate_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('estimate_name'=>$_smarty_tpl->tpl_vars['context']->value->getName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Estimate updated for :estimate_name from :company_name.<?php $_block_repeat=false;
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
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>: <span style="color:#999999"><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['old_total']->value,$_smarty_tpl->tpl_vars['context']->value->getCurrency(),$_smarty_tpl->tpl_vars['language']->value,true,true),$_smarty_tpl);?>
</span> <?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['context']->value->getTotal(),$_smarty_tpl->tpl_vars['context']->value->getCurrency(),$_smarty_tpl->tpl_vars['language']->value,true,true),$_smarty_tpl);?>

</h1>
<p><a href="<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getPublicUrl(),$_smarty_tpl);?>
" rel="nofollow"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>View the updated estimate<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></a></p>
<p><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>To view the updated estimate, visit the above link or open the attached PDF. You can discuss the estimate by replying directly to this email.<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></p>

<!-- Metadata -->
<div class="metadata" style="color: #999999; font-size: 14px; line-height: 21px;">
    <p><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('sender_name'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('sender_name'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Sent by :sender_name from :company_name to<?php $_block_repeat=false;
echo smarty_block_lang(array('sender_name'=>$_smarty_tpl->tpl_vars['sender']->value->getDisplayName(),'company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?> <?php echo smarty_modifier_notification_recipients($_smarty_tpl->tpl_vars['context']->value->getRecipientInstances(),$_smarty_tpl->tpl_vars['sender']->value,'color: #999999; text-decoration: none;',$_smarty_tpl->tpl_vars['language']->value);?>
</p>
</div><?php }
}
