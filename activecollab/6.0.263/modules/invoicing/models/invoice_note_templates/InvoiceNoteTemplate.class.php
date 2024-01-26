<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

class InvoiceNoteTemplate extends BaseInvoiceNoteTemplate
{
    public function jsonSerialize()
    {
        $result = parent::jsonSerialize();

        $result['name'] = $this->getName();
        $result['content'] = $this->getContent();
        $result['is_default'] = $this->getIsDefault();

        return $result;
    }

    public function getRoutingContext(): string
    {
        return 'invoice_note_template';
    }

    public function getRoutingContextParams(): array
    {
        return [
            'invoice_note_template_id' => $this->getId(),
        ];
    }

    /**
     * Returns true if $user can view this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canView(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Returns true if $user can update this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return $user->isFinancialManager();
    }

    /**
     * Returns true if $user can delete or move to trash this object.
     *
     * @param  User $user
     * @return bool
     */
    public function canDelete(User $user)
    {
        return $user->isFinancialManager();
    }

    public function validate(ValidationErrors &$errors)
    {
        $this->validatePresenceOf('name') or $errors->fieldValueIsRequired('name');
        $this->validateUniquenessOf('name') or $errors->fieldValueNeedsToBeUnique('name');
        $this->validatePresenceOf('content') or $errors->fieldValueIsRequired('content');

        return parent::validate($errors);
    }
}
