<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

/**
 * InvoiceNoteTemplates class.
 *
 * @package ActiveCollab.modules.invoicing
 * @subpackage models
 */
class InvoiceNoteTemplates extends BaseInvoiceNoteTemplates
{
    /**
     * Get Default invoice note template.
     *
     * @return InvoiceNoteTemplate
     */
    public static function getDefault()
    {
        return self::find(['conditions' => ['is_default = ?', true], 'one' => true]);
    }

    /**
     * Set default invoice note template.
     *
     * @param  InvoiceNoteTemplate      $note_template
     * @return InvoiceNoteTemplate|bool
     */
    public static function setDefault(InvoiceNoteTemplate $note_template = null)
    {
        if ($note_template && $note_template->getIsDefault()) {
            return $note_template;
        }

        DB::transact(function () use ($note_template) {
            DB::execute('UPDATE invoice_note_templates SET is_default = ?', false);

            if ($note_template) {
                DB::execute('UPDATE invoice_note_templates SET is_default = ? WHERE id = ?', true, $note_template->getId());
            }
        });

        self::clearCache();

        return $note_template ? DataObjectPool::reload('InvoiceNoteTemplate', $note_template->getId()) : true;
    }

    // ---------------------------------------------------
    //  Permissions
    // ---------------------------------------------------

    /**
     * Returns true if $user can create a new instance of this type.
     *
     * @param  User $user
     * @return bool
     */
    public static function canAdd(User $user)
    {
        return $user->isOwner();
    }
}
