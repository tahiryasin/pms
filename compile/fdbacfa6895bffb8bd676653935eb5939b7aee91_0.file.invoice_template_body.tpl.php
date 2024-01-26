<?php
/* Smarty version 3.1.33, created on 2023-12-09 11:15:43
  from 'D:\Work\projects\nr10\projects\activecollab\6.2.169\modules\invoicing\resources\invoice_template_body.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_65744c5fdae6a8_85023927',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fdbacfa6895bffb8bd676653935eb5939b7aee91' => 
    array (
      0 => 'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\modules\\invoicing\\resources\\invoice_template_body.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_65744c5fdae6a8_85023927 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\environment\\helpers\\block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'D:\\Work\\projects\\nr10\\projects\\activecollab\\6.2.169\\angie\\frameworks\\environment\\helpers\\modifier.money.php','function'=>'smarty_modifier_money',),));
?>
<style>
    .overall {
        font-family: '<?php echo clean($_smarty_tpl->tpl_vars['template']->value->getFont(),$_smarty_tpl);?>
';
        font-size: <?php echo clean($_smarty_tpl->tpl_vars['template']->value->getFontSize(),$_smarty_tpl);?>
mm;
        line-height: <?php echo clean($_smarty_tpl->tpl_vars['template']->value->getLineHeight(),$_smarty_tpl);?>
mm;
    }

    h1 {
        font-size: 5.5mm;
        font-weight: bold;
    }

    .overdue {
        color: #FF0000;
    }

    .invoice_details_right {
        text-align: right;
    }

    .company_details {
        text-align: right;
    }

    .company_details_left {
        text-align: left;
    }

    .item_table {
        padding: 1.3mm 0mm 1.3mm 0mm;
    }

    .item_table_cell {
        line-height: 4.5mm;
        height: 4.5mm;
    }

    .item_table_order {
        text-align: left;
        width: <?php echo clean($_smarty_tpl->tpl_vars['columns']->value['order'],$_smarty_tpl);?>
mm;
    }

    .item_table_description {
        text-align: left;
        width: <?php echo clean($_smarty_tpl->tpl_vars['columns']->value['description'],$_smarty_tpl);?>
mm;
    }

    .item_table_numeric {
        text-align: right;
        width: <?php echo clean($_smarty_tpl->tpl_vars['columns']->value['numeric'],$_smarty_tpl);?>
mm;
    }

    .stronger_border {
        border-bottom: 0.2mm solid #bbbbbb;
    }

    .bold {
        font-weight: bold;
    }

    .lighter_border {
        border-bottom: 0.2mm solid #eeeeee;
    }

    .totals_table_empty {
        width: <?php echo clean($_smarty_tpl->tpl_vars['columns']->value['totals_empty'],$_smarty_tpl);?>
mm;
    }

    .totals_table_label {
        width: <?php echo clean($_smarty_tpl->tpl_vars['columns']->value['totals_label'],$_smarty_tpl);?>
mm;
        text-align: right;
    }

    .totals_table_value {
        width: <?php echo clean($_smarty_tpl->tpl_vars['columns']->value['numeric'],$_smarty_tpl);?>
mm;
        text-align: right;
    }

    .invoice_note {
        line-height: 3.2mm;
    }
</style>

<div class="overall"><table>
        <tr>
            <?php if (!$_smarty_tpl->tpl_vars['template']->value->getBodyLayout()) {?>
                <td class="company_details company_details_left"><b><?php echo clean($_smarty_tpl->tpl_vars['invoice']->value->getCompanyName(),$_smarty_tpl);?>
</b><br/><?php echo nl2br(clean($_smarty_tpl->tpl_vars['invoice']->value->getCompanyAddress()));?>
</td>
            <?php }?>

            <td class="invoice_details <?php if (!$_smarty_tpl->tpl_vars['template']->value->getBodyLayout()) {?>invoice_details_right<?php }?>"><h1><?php if ($_smarty_tpl->tpl_vars['invoice']->value instanceof Invoice) {
$_prefixVariable1 = $_smarty_tpl->tpl_vars['template']->value->getLabel();
$_smarty_tpl->_assignInScope('label', $_prefixVariable1);
if ($_prefixVariable1) {
echo clean($_smarty_tpl->tpl_vars['label']->value,$_smarty_tpl);
} else {
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Invoice<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
}
}
if ($_smarty_tpl->tpl_vars['invoice']->value instanceof Estimate) {
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Estimate for<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
}?> <?php echo clean($_smarty_tpl->tpl_vars['invoice']->value->getName(),$_smarty_tpl);?>
</h1>

                <?php if ($_smarty_tpl->tpl_vars['invoice']->value instanceof Invoice) {?>
                    <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getPurchaseOrderNumber()) {?>
                        <br/>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('po_number'=>$_smarty_tpl->tpl_vars['invoice']->value->getPurchaseOrderNumber(),'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('po_number'=>$_smarty_tpl->tpl_vars['invoice']->value->getPurchaseOrderNumber(),'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Reference: :po_number<?php $_block_repeat=false;
echo smarty_block_lang(array('po_number'=>$_smarty_tpl->tpl_vars['invoice']->value->getPurchaseOrderNumber(),'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>

                    <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getProject() instanceof Project) {?>
                        <br/>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('project_name'=>$_smarty_tpl->tpl_vars['invoice']->value->getProject()->getName(),'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('project_name'=>$_smarty_tpl->tpl_vars['invoice']->value->getProject()->getName(),'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Project: :project_name<?php $_block_repeat=false;
echo smarty_block_lang(array('project_name'=>$_smarty_tpl->tpl_vars['invoice']->value->getProject()->getName(),'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>

                    <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getStatus() == 'issued' || $_smarty_tpl->tpl_vars['invoice']->value->getStatus() == 'paid') {?>
                        <?php if ($_smarty_tpl->tpl_vars['issued_on']->value) {?>
                            <br/>
                            <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('issued_date'=>$_smarty_tpl->tpl_vars['issued_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('issued_date'=>$_smarty_tpl->tpl_vars['issued_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Issued On: :issued_date<?php $_block_repeat=false;
echo smarty_block_lang(array('issued_date'=>$_smarty_tpl->tpl_vars['issued_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                        <?php }?>

                        <?php if ($_smarty_tpl->tpl_vars['due_on']->value) {?>
                            <br/>
                            <span
                                    class="<?php if ('due_on' && $_smarty_tpl->tpl_vars['invoice']->value->isOverdue()) {?>overdue<?php }?>"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('due_date'=>$_smarty_tpl->tpl_vars['due_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('due_date'=>$_smarty_tpl->tpl_vars['due_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Payment Due On: :due_date<?php $_block_repeat=false;
echo smarty_block_lang(array('due_date'=>$_smarty_tpl->tpl_vars['due_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></span>
                        <?php }?>
                    <?php }?>

                    <?php if ($_smarty_tpl->tpl_vars['related_projects']->value && (count($_smarty_tpl->tpl_vars['related_projects']->value) > 0)) {?>
                        <br/>
                        <span><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Related project(s)<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>: <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['related_projects']->value, 'v', false, 'k');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['k']->value => $_smarty_tpl->tpl_vars['v']->value) {
?> <?php echo clean($_smarty_tpl->tpl_vars['v']->value->getName(),$_smarty_tpl);?>
 (#<?php echo clean($_smarty_tpl->tpl_vars['k']->value,$_smarty_tpl);?>
) <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></span>
                    <?php }?>
                    <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getIsCanceled()) {?>
                        <br/>
                        <span
                                class="overdue"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('canceled_date'=>$_smarty_tpl->tpl_vars['canceled_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('canceled_date'=>$_smarty_tpl->tpl_vars['canceled_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Canceled On: :canceled_date<?php $_block_repeat=false;
echo smarty_block_lang(array('canceled_date'=>$_smarty_tpl->tpl_vars['canceled_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></span>
                    <?php }?>
                <?php }?>

                <?php if ($_smarty_tpl->tpl_vars['invoice']->value instanceof Estimate) {?>
                    <?php if ($_smarty_tpl->tpl_vars['sent_on']->value) {?>
                        <br/>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('sent_date'=>$_smarty_tpl->tpl_vars['sent_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('sent_date'=>$_smarty_tpl->tpl_vars['sent_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Sent On: :sent_date<?php $_block_repeat=false;
echo smarty_block_lang(array('sent_date'=>$_smarty_tpl->tpl_vars['sent_on']->value,'language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>
                <?php }?>
            </td>

            <?php if ($_smarty_tpl->tpl_vars['template']->value->getBodyLayout()) {?>
                <td class="company_details"><b><?php echo clean($_smarty_tpl->tpl_vars['invoice']->value->getCompanyName(),$_smarty_tpl);?>
</b><br/><?php echo nl2br(clean($_smarty_tpl->tpl_vars['invoice']->value->getCompanyAddress()));?>
</td>
            <?php }?>
        </tr>
    </table>

    <br/><br/><br/>

    <table border="0" class="item_table">
        <tr>
            <th class="item_table_order item_table_cell stronger_border bold">#</th>
            <th class="item_table_description item_table_cell stronger_border bold"
                ><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Description<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></th>
            <th class="item_table_numeric item_table_cell stronger_border bold"
                ><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Qty.<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></th>
            <th class="item_table_numeric item_table_cell stronger_border bold"
                ><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Unit Cost<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></th>
            <th class="item_table_numeric item_table_cell stronger_border bold"
                ><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Amount<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></th>
        </tr>
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['invoice']->value->getItems(), 'item', false, NULL, 'item_loop', array (
  'iteration' => true,
));
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
$_smarty_tpl->tpl_vars['__smarty_foreach_item_loop']->value['iteration']++;
?>
            <?php if ((isset($_smarty_tpl->tpl_vars['__smarty_foreach_item_loop']->value['iteration']) ? $_smarty_tpl->tpl_vars['__smarty_foreach_item_loop']->value['iteration'] : null) != count($_smarty_tpl->tpl_vars['invoice']->value->getItems())) {?>
                <?php $_smarty_tpl->_assignInScope('item_border', "lighter_border");?>
            <?php } else { ?>
                <?php $_smarty_tpl->_assignInScope('item_border', "stronger_border");?>
            <?php }?>
            <tr>
                <td class="item_table_order item_table_cell <?php echo clean($_smarty_tpl->tpl_vars['item_border']->value,$_smarty_tpl);?>
"><?php echo clean((isset($_smarty_tpl->tpl_vars['__smarty_foreach_item_loop']->value['iteration']) ? $_smarty_tpl->tpl_vars['__smarty_foreach_item_loop']->value['iteration'] : null),$_smarty_tpl);?>
</td>
                <td class="item_table_description item_table_cell <?php echo clean($_smarty_tpl->tpl_vars['item_border']->value,$_smarty_tpl);?>
"><?php echo nl2br(clean($_smarty_tpl->tpl_vars['item']->value->getDescription()));?>
</td>
                <td class="item_table_numeric item_table_cell <?php echo clean($_smarty_tpl->tpl_vars['item_border']->value,$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['item']->value->getQuantity(),$_smarty_tpl);?>
</td>
                <td class="item_table_numeric item_table_cell <?php echo clean($_smarty_tpl->tpl_vars['item_border']->value,$_smarty_tpl);?>
"><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['item']->value->getUnitCost(),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
                <td class="item_table_numeric item_table_cell <?php echo clean($_smarty_tpl->tpl_vars['item_border']->value,$_smarty_tpl);?>
"><?php echo clean(smarty_modifier_money(($_smarty_tpl->tpl_vars['item']->value->getSubtotal()),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
            </tr>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
    </table>

    <table border="0" class="item_table">
        <tr>
            <td class="item_table_cell totals_table_empty"></td>
            <td class="item_table_cell totals_table_label lighter_border"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Subtotal<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                :
            </td>
            <td class="item_table_cell totals_table_value lighter_border"><?php echo clean(smarty_modifier_money(($_smarty_tpl->tpl_vars['invoice']->value->getSubTotal()),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
        </tr>
        <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getDiscount()) {?>
            <tr>
                <td class="item_table_cell totals_table_empty"></td>
                <td class="item_table_cell totals_table_label lighter_border"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Discount<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    :
                </td>
                <td class="item_table_cell totals_table_value lighter_border">
                    -<?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['invoice']->value->getDiscount(),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
            </tr>
        <?php }?>

        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['invoice']->value->getTaxGroupedByType(), 'invoice_tax');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['invoice_tax']->value) {
?>
            <tr>
                <td class="item_table_cell totals_table_empty"></td>
                <td class="item_table_cell totals_table_label lighter_border"><?php echo clean($_smarty_tpl->tpl_vars['invoice_tax']->value['name'],$_smarty_tpl);?>

                    (<?php echo clean($_smarty_tpl->tpl_vars['invoice_tax']->value['percentage'],$_smarty_tpl);?>
%):
                </td>
                <td class="item_table_cell totals_table_value lighter_border"><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['invoice_tax']->value['amount'],$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
            </tr>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getRoundingDifference()) {?>
            <tr>
                <td class="item_table_cell totals_table_empty"></td>
                <td class="item_table_cell totals_table_label lighter_border"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Rounding Diff.<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    :
                </td>
                <td class="item_table_cell totals_table_value lighter_border"><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['invoice']->value->getRoundingDifference(),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
            </tr>
        <?php }?>
        <tr>
            <td class="item_table_cell totals_table_empty"></td>
            <td class="item_table_cell totals_table_label stronger_border">
                <b><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Total<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?> (<?php echo clean($_smarty_tpl->tpl_vars['invoice']->value->getCurrency()->getCode(),$_smarty_tpl);?>
):</b></td>
            <td class="item_table_cell totals_table_value stronger_border">
                <b><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['invoice']->value->getRoundedTotal(),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</b></td>
        </tr>
        <?php if (!($_smarty_tpl->tpl_vars['invoice']->value instanceof Estimate)) {?>
            <tr>
                <td class="item_table_cell totals_table_empty"></td>
                <td class="item_table_cell totals_table_label lighter_border"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Amount Paid<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    :
                </td>
                <td class="item_table_cell totals_table_value lighter_border"><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['invoice']->value->getPaidAmount(),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</td>
            </tr>
            <tr>
                <td class="item_table_cell totals_table_empty"></td>
                <td class="item_table_cell totals_table_label stronger_border">
                    <b><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Balance Due<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?> (<?php echo clean($_smarty_tpl->tpl_vars['invoice']->value->getCurrency()->getCode(),$_smarty_tpl);?>
):</b></td>
                <td class="item_table_cell totals_table_value stronger_border">
                    <b><?php echo clean(smarty_modifier_money($_smarty_tpl->tpl_vars['invoice']->value->getBalanceDue(),$_smarty_tpl->tpl_vars['invoice']->value->getCurrency(),$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()),$_smarty_tpl);?>
</b></td>
            </tr>
        <?php }?>
    </table>

    <?php if ($_smarty_tpl->tpl_vars['invoice']->value->getNote()) {?>
        <br/>
        <br/>
        <br/>
        <b><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Note<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['invoice']->value->getLanguage()), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>:</b>
        <br/>
        <span class="invoice_note"><?php echo nl2br(clean($_smarty_tpl->tpl_vars['invoice']->value->getNote()));?>
</span>
    <?php }?>
</div><?php }
}
