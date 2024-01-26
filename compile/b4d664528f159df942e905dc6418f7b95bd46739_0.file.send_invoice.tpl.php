<?php
/* Smarty version 3.1.33, created on 2023-12-09 12:27:42
  from 'D:\Work\projects\nr10\projects\activecollab\6.2.169\modules\invoicing\notifications\email\send_invoice.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_65745d3e60eae3_12133055',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'b4d664528f159df942e905dc6418f7b95bd46739' => 
    array (
      0 => 'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\modules\\invoicing\\notifications\\email\\send_invoice.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_65745d3e60eae3_12133055 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\environment\\helpers\\block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\environment\\helpers\\modifier.money.php','function'=>'smarty_modifier_money',),2=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\email\\helpers\\modifier.notification_recipients.php','function'=>'smarty_modifier_notification_recipients',),));
if ($_smarty_tpl->tpl_vars['custom_subject']->value) {?>
    <?php echo $_smarty_tpl->tpl_vars['custom_subject']->value;?>

<?php } else { ?>
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Invoice from :company_name<?php $_block_repeat=false;
echo smarty_block_lang(array('company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
}?>
================================================================================

<?php if ($_smarty_tpl->tpl_vars['custom_message']->value) {?>
    <p><?php echo nl2br(htmlspecialchars($_smarty_tpl->tpl_vars['custom_message']->value, ENT_QUOTES, 'UTF-8', true));?>
</p>
<?php } else { ?>
    <h1 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 16px;">
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Invoice from :company_name.<?php $_block_repeat=false;
echo smarty_block_lang(array('company_name'=>$_smarty_tpl->tpl_vars['owner_company']->value->getName(),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?><br/>
    </h1>
<?php }?>

<p>
    <strong><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Invoice No<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>:</strong> <?php echo clean($_smarty_tpl->tpl_vars['context']->value->getNumber(),$_smarty_tpl);?>
 <br>
    <strong><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Client<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>:</strong> <?php echo clean($_smarty_tpl->tpl_vars['context']->value->getCompanyName(),$_smarty_tpl);?>
 <br>
    <strong><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Amount<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>:</strong> <?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['context']->value->getBalanceDue(),$_smarty_tpl->tpl_vars['context']->value->getCurrency(),$_smarty_tpl->tpl_vars['language']->value,true),$_smarty_tpl);?>

    <br>
    <strong><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Due on<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>:</strong> <?php echo clean($_smarty_tpl->tpl_vars['context']->value->getDueOn()->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),$_smarty_tpl);?>

</p>

<?php if ($_smarty_tpl->tpl_vars['context']->value->canMakePayment()) {?>
    <p><a href="<?php echo clean($_smarty_tpl->tpl_vars['context']->value->getPublicUrl($_smarty_tpl->tpl_vars['recipient']->value),$_smarty_tpl);?>
" rel="nofollow"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Click here to make the payment<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></a></p>
<?php }?>

<p><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>To view the invoice details, open the attached PDF.<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></p>

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
</div>
<?php }
}
