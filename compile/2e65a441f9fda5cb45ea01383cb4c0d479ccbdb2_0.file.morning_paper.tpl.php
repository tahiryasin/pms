<?php
/* Smarty version 3.1.33, created on 2023-12-04 07:00:05
  from '/home/babydoshop/projects/activecollab/6.2.169/modules/system/notifications/email/morning_paper.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_656d78f5dfc0a1_93434091',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '2e65a441f9fda5cb45ea01383cb4c0d479ccbdb2' => 
    array (
      0 => '/home/babydoshop/projects/activecollab/6.2.169/modules/system/notifications/email/morning_paper.tpl',
      1 => 1701516378,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_656d78f5dfc0a1_93434091 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/block.lang.php','function'=>'smarty_block_lang',),1=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/modifier.date.php','function'=>'smarty_modifier_date',),2=>array('file'=>'/home/babydoshop/projects/activecollab/6.2.169/angie/frameworks/environment/helpers/modifier.time_vs_system_time.php','function'=>'smarty_modifier_time_vs_system_time',),));
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('day'=>$_smarty_tpl->tpl_vars['paper_day']->value->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('day'=>$_smarty_tpl->tpl_vars['paper_day']->value->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Daily report for :day<?php $_block_repeat=false;
echo smarty_block_lang(array('day'=>$_smarty_tpl->tpl_vars['paper_day']->value->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
================================================================================
<h1 style="font-size: 16px; margin-top: 20px; margin-bottom: 16px;">
    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('recipient_name'=>$_smarty_tpl->tpl_vars['recipient']->value->getFirstName(true),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('recipient_name'=>$_smarty_tpl->tpl_vars['recipient']->value->getFirstName(true),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Good morning, :recipient_name!<?php $_block_repeat=false;
echo smarty_block_lang(array('recipient_name'=>$_smarty_tpl->tpl_vars['recipient']->value->getFirstName(true),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?><br>
    <span
        style="color: #999999; font-weight: normal;"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('day'=>$_smarty_tpl->tpl_vars['paper_day']->value->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('day'=>$_smarty_tpl->tpl_vars['paper_day']->value->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>This is the daily recap for :day<?php $_block_repeat=false;
echo smarty_block_lang(array('day'=>$_smarty_tpl->tpl_vars['paper_day']->value->formatForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></span>
</h1>

<?php if ($_smarty_tpl->tpl_vars['late_data']->value) {?>
    <h2 style="font-weight: normal; font-size: 18px; border-bottom: 1px solid #666; padding-bottom: 6px; padding-top: 12px;">
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Late<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
    </h2>
    <p>
        <strong style="font-size: 13px;">
            <?php if ($_smarty_tpl->tpl_vars['late_data']->value['late_tasks_count'] === 1) {?>
                <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 task is late:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
            <?php } else { ?>
                <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>$_smarty_tpl->tpl_vars['late_data']->value['late_tasks_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['late_data']->value['late_tasks_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num tasks are late:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['late_data']->value['late_tasks_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
            <?php }?>
        </strong><br>

        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['late_data']->value['tasks_by_project'], 'project_details');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['project_details']->value) {
?>
            <span style="font-size: 13px; font-weight: bold; color: #999999;"><?php echo clean($_smarty_tpl->tpl_vars['project_details']->value['name'],$_smarty_tpl);?>
</span>
            <br>
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['tasks'], 'task');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['task']->value) {
?>
                &#10065;
                <a href="<?php echo clean($_smarty_tpl->tpl_vars['task']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['task']->value['name'],$_smarty_tpl);?>
</a>
                <span
                    style="color: #ff0000;"><?php if ($_smarty_tpl->tpl_vars['task']->value['diff'] === -1) {
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 day late<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
} else {
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>abs($_smarty_tpl->tpl_vars['task']->value['diff']),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>abs($_smarty_tpl->tpl_vars['task']->value['diff']),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num days late<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>abs($_smarty_tpl->tpl_vars['task']->value['diff']),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);
}?></span>
                <br>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
    </p>
<?php }?>


<?php if ($_smarty_tpl->tpl_vars['today_data']->value) {?>
    <h2 style="font-weight: normal; font-size: 18px; border-bottom: 1px solid #666; padding-bottom: 6px; padding-top: 12px;">
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Today<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
    </h2>

    <?php if ($_smarty_tpl->tpl_vars['today_data']->value['availability_records_count'] > 0) {?>
        <p>
            <strong style="font-size: 13px;">
                <?php if ($_smarty_tpl->tpl_vars['today_data']->value['availability_records_count'] === 1) {?>
                    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>One person unavailable:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                <?php } else { ?>
                    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['availability_records_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['availability_records_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num people unavailable:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['availability_records_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                <?php }?>
            </strong> <br>

            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['today_data']->value['availability_records'], 'availability_record');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['availability_record']->value) {
?>
                <span>
                    <a style="display: inline-block" href="<?php echo clean($_smarty_tpl->tpl_vars['availability_record']->value['user_link'],$_smarty_tpl);?>
">
                        <?php echo clean($_smarty_tpl->tpl_vars['availability_record']->value['user_name'],$_smarty_tpl);?>

                    </a>,
                    <?php if ($_smarty_tpl->tpl_vars['availability_record']->value['end_date']->getTimestamp() === $_smarty_tpl->tpl_vars['availability_record']->value['start_date']->getTimestamp()) {?>
                        <?php echo clean($_smarty_tpl->tpl_vars['availability_record']->value['availability_type_name'],$_smarty_tpl);?>
 (<?php echo clean(smarty_modifier_date($_smarty_tpl->tpl_vars['availability_record']->value['end_date'],0,$_smarty_tpl->tpl_vars['recipient']->value,$_smarty_tpl->tpl_vars['language']->value),$_smarty_tpl);?>
)
                    <?php } else { ?>
                        <?php echo clean($_smarty_tpl->tpl_vars['availability_record']->value['availability_type_name'],$_smarty_tpl);?>
 (<?php echo clean(smarty_modifier_date($_smarty_tpl->tpl_vars['availability_record']->value['start_date'],0,$_smarty_tpl->tpl_vars['recipient']->value,$_smarty_tpl->tpl_vars['language']->value),$_smarty_tpl);?>
 - <?php echo clean(smarty_modifier_date($_smarty_tpl->tpl_vars['availability_record']->value['end_date'],0,$_smarty_tpl->tpl_vars['recipient']->value,$_smarty_tpl->tpl_vars['language']->value),$_smarty_tpl);?>
)
                    <?php }?>
                </span>
                <br>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        </p>
    <?php }?>


    <?php if ($_smarty_tpl->tpl_vars['today_data']->value['calendar_events_count'] > 0) {?>
        <p>
            <strong style="font-size: 13px;">
                <?php if ($_smarty_tpl->tpl_vars['today_data']->value['calendar_events_count'] === 1) {?>
                    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 event scheduled:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                <?php } else { ?>
                    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['calendar_events_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['calendar_events_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num events scheduled:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['calendar_events_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                <?php }?>
            </strong> <br>

            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['today_data']->value['calendar_events_by_calendar'], 'calendar_details');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['calendar_details']->value) {
?>
                <span style="font-size: 13px; font-weight: bold; color: #999999;"><?php echo clean($_smarty_tpl->tpl_vars['calendar_details']->value['name'],$_smarty_tpl);?>
</span>
                <br>
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['calendar_details']->value['calendar_events'], 'calendar_event');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['calendar_event']->value) {
?>
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['calendar_event']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['calendar_event']->value['name'],$_smarty_tpl);?>
</a>
                    <?php if ($_smarty_tpl->tpl_vars['calendar_event']->value['time']) {?> at <?php echo clean(smarty_modifier_time_vs_system_time($_smarty_tpl->tpl_vars['calendar_event']->value['time'],$_smarty_tpl->tpl_vars['recipient']->value),$_smarty_tpl);?>
<span style="color: #ff0000;">&#9873;</span><?php }?>
                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        </p>
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['today_data']->value['due_tasks_count'] > 0) {?>
        <p>
            <strong style="font-size: 13px;">
                <?php if ($_smarty_tpl->tpl_vars['today_data']->value['due_tasks_count'] === 1) {?>
                    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 task is due:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                <?php } else { ?>
                    <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['due_tasks_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['due_tasks_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num tasks are due:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>$_smarty_tpl->tpl_vars['today_data']->value['due_tasks_count'],'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                <?php }?>
            </strong><br>

            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['today_data']->value['tasks_by_project'], 'project_details');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['project_details']->value) {
?>
                <span style="font-size: 13px; font-weight: bold; color: #999999;"><?php echo clean($_smarty_tpl->tpl_vars['project_details']->value['name'],$_smarty_tpl);?>
</span>
                <br>
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['tasks'], 'task');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['task']->value) {
?>
                    &#10065;
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['task']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['task']->value['name'],$_smarty_tpl);?>
</a>
                    <?php if ($_smarty_tpl->tpl_vars['task']->value['due_on']->getTimestamp() <= strtotime('today')) {?>
                        <?php $_smarty_tpl->_assignInScope('color', '#ff0000');?>                     <?php } else { ?>
                        <?php $_smarty_tpl->_assignInScope('color', '#000000');?>
                    <?php }?>

                    <?php if ($_smarty_tpl->tpl_vars['task']->value['due_on'] && $_smarty_tpl->tpl_vars['task']->value['due_on']->isToday()) {?>
                        <span style="color: <?php echo clean($_smarty_tpl->tpl_vars['color']->value,$_smarty_tpl);?>
"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Due Today<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></span>
                    <?php } elseif ($_smarty_tpl->tpl_vars['task']->value['start_on']) {?>
                        <?php if ($_smarty_tpl->tpl_vars['task']->value['start_on']->isToday()) {?>
                            <span style="color: <?php echo clean($_smarty_tpl->tpl_vars['color']->value,$_smarty_tpl);?>
;"><?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Start Today<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?></span>
                        <?php } else { ?>
                            <span style="color: <?php echo clean($_smarty_tpl->tpl_vars['color']->value,$_smarty_tpl);?>
;">
                              <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('due_on'=>$_smarty_tpl->tpl_vars['task']->value['due_on']->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'start_on'=>$_smarty_tpl->tpl_vars['task']->value['start_on']->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('due_on'=>$_smarty_tpl->tpl_vars['task']->value['due_on']->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'start_on'=>$_smarty_tpl->tpl_vars['task']->value['start_on']->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:start_on &mdash; :due_on<?php $_block_repeat=false;
echo smarty_block_lang(array('due_on'=>$_smarty_tpl->tpl_vars['task']->value['due_on']->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'start_on'=>$_smarty_tpl->tpl_vars['task']->value['start_on']->formatDateForUser($_smarty_tpl->tpl_vars['recipient']->value,0,$_smarty_tpl->tpl_vars['language']->value),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                            </span>
                        <?php }?>
                    <?php }?>

                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
        </p>
    <?php }
}?>

<?php if ($_smarty_tpl->tpl_vars['prev_data']->value) {?>
    <h2 style="font-weight: normal; font-size: 18px; border-bottom: 1px solid #666; padding-bottom: 6px; padding-top: 12px;">
        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>Earlier<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
    </h2>
    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['prev_data']->value, 'project_details', false, 'project_id');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['project_id']->value => $_smarty_tpl->tpl_vars['project_details']->value) {
?>
        <h3 style="font-size: 16px;"><a href="<?php echo clean($_smarty_tpl->tpl_vars['project_details']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['project_details']->value['name'],$_smarty_tpl);?>
</a></h3>
        <?php if (isset($_smarty_tpl->tpl_vars['project_details']->value['task_completed'])) {?>
            <p>
                <strong style="font-size: 13px;">
                    <?php if (count($_smarty_tpl->tpl_vars['project_details']->value['task_completed']) == 1) {?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 task completed:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php } else { ?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['task_completed']),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['task_completed']),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num tasks completed:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['task_completed']),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>
                </strong><br>

                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['task_completed'], 'task');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['task']->value) {
?>
                    &#10004;
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['task']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['task']->value['name'],$_smarty_tpl);?>
</a>
                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </p>
        <?php }?>

        <?php if (isset($_smarty_tpl->tpl_vars['project_details']->value['task_created'])) {?>
            <p>
                <strong style="font-size: 13px;">
                    <?php if (count($_smarty_tpl->tpl_vars['project_details']->value['task_created']) == 1) {?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 new task added:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php } else { ?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['task_created']),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['task_created']),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num new tasks added:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['task_created']),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>
                </strong><br>

                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['task_created'], 'task');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['task']->value) {
?>
                    &#10065;
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['task']->value['permalink'],$_smarty_tpl);?>
"<?php if ($_smarty_tpl->tpl_vars['task']->value['is_completed']) {?> style="text-decoration: line-through"<?php }?>><?php echo clean($_smarty_tpl->tpl_vars['task']->value['name'],$_smarty_tpl);?>
</a>
                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </p>
        <?php }?>

        <?php if (isset($_smarty_tpl->tpl_vars['project_details']->value['object_discussed'])) {?>
            <p>
                <strong style="font-size: 13px;">
                    <?php if (count($_smarty_tpl->tpl_vars['project_details']->value['object_discussed']) == 1) {?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 thing discussed:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php } else { ?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['object_discussed']),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['object_discussed']),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num things discussed:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['object_discussed']),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>
                </strong><br>

                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['object_discussed'], 'object');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['object']->value) {
?>
                    &#8226;
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['object']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['object']->value['name'],$_smarty_tpl);?>
</a>
                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </p>
        <?php }?>

        <?php if (isset($_smarty_tpl->tpl_vars['project_details']->value['note_created'])) {?>
            <p>
                <strong style="font-size: 13px;">
                    <?php if (count($_smarty_tpl->tpl_vars['project_details']->value['note_created']) == 1) {?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 new note added:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php } else { ?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['note_created']),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['note_created']),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num new notes added:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['note_created']),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>
                </strong><br>

                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['note_created'], 'note');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['note']->value) {
?>
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['note']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['note']->value['name'],$_smarty_tpl);?>
</a>
                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </p>
        <?php }?>

        <?php if (isset($_smarty_tpl->tpl_vars['project_details']->value['file_uploaded'])) {?>
            <p>
                <strong style="font-size: 13px;">
                    <?php if (count($_smarty_tpl->tpl_vars['project_details']->value['file_uploaded']) == 1) {?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>1 file uploaded:<?php $_block_repeat=false;
echo smarty_block_lang(array('language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php } else { ?>
                        <?php $_smarty_tpl->smarty->_cache['_tag_stack'][] = array('lang', array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['file_uploaded']),'language'=>$_smarty_tpl->tpl_vars['language']->value));
$_block_repeat=true;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['file_uploaded']),'language'=>$_smarty_tpl->tpl_vars['language']->value), null, $_smarty_tpl, $_block_repeat);
while ($_block_repeat) {
ob_start();?>:num files uploaded:<?php $_block_repeat=false;
echo smarty_block_lang(array('num'=>count($_smarty_tpl->tpl_vars['project_details']->value['file_uploaded']),'language'=>$_smarty_tpl->tpl_vars['language']->value), ob_get_clean(), $_smarty_tpl, $_block_repeat);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>
                    <?php }?>
                </strong><br>

                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['project_details']->value['file_uploaded'], 'file');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['file']->value) {
?>
                    <a href="<?php echo clean($_smarty_tpl->tpl_vars['file']->value['permalink'],$_smarty_tpl);?>
"><?php echo clean($_smarty_tpl->tpl_vars['file']->value['name'],$_smarty_tpl);?>
</a>
                    <br>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
            </p>
        <?php }?>
    <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
}
}
}
