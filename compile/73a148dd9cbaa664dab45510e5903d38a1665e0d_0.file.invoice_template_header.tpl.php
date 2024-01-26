<?php
/* Smarty version 3.1.33, created on 2023-12-02 11:36:12
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/invoicing/resources/invoice_template_header.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656b16ac32a397_06375975',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '73a148dd9cbaa664dab45510e5903d38a1665e0d' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/invoicing/resources/invoice_template_header.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656b16ac32a397_06375975 (Smarty_Internal_Template $_smarty_tpl) {
?><style>
    .overall {
        font-family: '<?php echo clean($_smarty_tpl->tpl_vars['template']->value->getFont(),$_smarty_tpl);?>
';
        font-size: <?php echo clean($_smarty_tpl->tpl_vars['template']->value->getFontSize(),$_smarty_tpl);?>
mm;
        line-height: <?php echo clean($_smarty_tpl->tpl_vars['template']->value->getLineHeight(),$_smarty_tpl);?>
mm;
    }

    .header_cell {
        width: 50%;
    }

    .company_cell_left {
        text-align: left;
    }

    .company_cell {
        text-align: right;
    }

    .logo_image {
        height: <?php echo clean($_smarty_tpl->tpl_vars['template']->value->getLogoImageHeight(3.779,21.166666667),$_smarty_tpl);?>
mm;
    }

    .logo_cell_right {
        text-align: right;
    }
    
    .empty_space {
        width: 30%;
    }
    
    .image_placeholder {
        width: 70%;
    }
    
</style>

<div class="overall"><table >
        <tr>
            <?php if ($_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?><td class="header_cell company_cell_left"><?php echo nl2br(clean($_smarty_tpl->tpl_vars['company_cell']->value));?>
</td><?php }?>
            <td class="header_cell <?php if ($_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?>logo_cell_right<?php } else { ?>logo_cell<?php }?>" ><table>
                    <tr>
                        <td class="<?php if ($_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?>empty_space<?php } else { ?>image_placeholder<?php }?>"><?php if (!$_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?><img src="@<?php echo clean(base64_encode(file_get_contents($_smarty_tpl->tpl_vars['template']->value->getLogoImagePath())),$_smarty_tpl);?>
" class="logo_image"/><?php }?></td>
                        <td class="<?php if ($_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?>image_placeholder<?php } else { ?>empty_space<?php }?>"><?php if ($_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?><img src="@<?php echo clean(base64_encode(file_get_contents($_smarty_tpl->tpl_vars['template']->value->getLogoImagePath())),$_smarty_tpl);?>
" class="logo_image"/><?php }?></td>
                    </tr>
                </table> 
            </td>
            <?php if (!$_smarty_tpl->tpl_vars['template']->value->getHeaderLayout()) {?><td class="header_cell company_cell"><?php echo nl2br(clean($_smarty_tpl->tpl_vars['company_cell']->value));?>
</td><?php }?>
        </tr>
    </table>
</div>
<?php }
}
