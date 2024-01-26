<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextImplementation;
use ActiveCollab\Foundation\Urls\Router\Context\RoutingContextInterface;

require_once APPLICATION_PATH . '/resources/ActiveCollabModuleModel.class.php';

/**
 * Discussions module model.
 *
 * @package activeCollab.modules.discussions
 * @subpackage models
 */
class DiscussionsModuleModel extends ActiveCollabModuleModel
{
    /**
     * Construct system module model definition.
     *
     * @param DiscussionsModule $parent
     */
    public function __construct(DiscussionsModule $parent)
    {
        parent::__construct($parent);

        $this->addModel(
            DB::createTable('discussions')->addColumns(
                [
                    new DBIdColumn(),
                    DBIntegerColumn::create('project_id', 10, 0)->setUnsigned(true),
                    DBNameColumn::create(150),
                    DBBodyColumn::create(),
                    new DBCreatedOnByColumn(),
                    new DBUpdatedOnByColumn(),
                    DBBoolColumn::create('is_hidden_from_clients'),
                    DBTrashColumn::create(true),
                ]
            )
        )
            ->implementComments(true, true)
            ->implementAttachments()
            ->implementHistory()
            ->implementAccessLog()
            ->implementSearch()
            ->implementTrash()
            ->implementActivityLog()
            ->addModelTrait(IHiddenFromClients::class)
            ->addModelTrait(IProjectElement::class, IProjectElementImplementation::class)
            ->addModelTraitTweak('IProjectElementImplementation::canViewAccessLogs insteadof IAccessLogImplementation')
            ->addModelTraitTweak('IProjectElementImplementation::whatIsWorthRemembering insteadof IActivityLogImplementation')
            ->addModelTrait(RoutingContextInterface::class, RoutingContextImplementation::class);
    }

    /**
     * Load initial framework data.
     */
    public function loadInitialData()
    {
        foreach (['discussions', 'tasks', 'files', 'notes'] as $t) {
            DB::execute("ALTER TABLE $t ADD last_comment_on DATETIME NULL");
        }

        DB::execute('CREATE TRIGGER default_last_comment_on_for_discussions BEFORE INSERT ON discussions FOR EACH ROW SET NEW.last_comment_on = NEW.created_on');

        DB::execute("CREATE TRIGGER project_element_comment_inserted AFTER INSERT ON comments FOR EACH ROW
            BEGIN
                IF NEW.parent_type = 'Task' THEN
                    UPDATE tasks SET last_comment_on = NEW.created_on WHERE id = NEW.parent_id;
                ELSEIF NEW.parent_type = 'Discussion' THEN
                    UPDATE discussions SET last_comment_on = NEW.created_on WHERE id = NEW.parent_id;
                ELSEIF NEW.parent_type = 'File' THEN
                    UPDATE files SET last_comment_on = NEW.created_on WHERE id = NEW.parent_id;
                ELSEIF NEW.parent_type = 'Note' THEN
                    UPDATE notes SET last_comment_on = NEW.created_on WHERE id = NEW.parent_id;
                END IF;
            END");

        DB::execute("CREATE TRIGGER project_element_comment_updated AFTER UPDATE ON comments FOR EACH ROW
            BEGIN
                IF NEW.parent_id = OLD.parent_id AND NEW.is_trashed != OLD.is_trashed THEN
                    IF NEW.parent_type = 'Task' THEN
                        UPDATE tasks SET last_comment_on = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'Task' AND parent_id = NEW.parent_id AND is_trashed = '0') WHERE id = NEW.parent_id;
                    ELSEIF NEW.parent_type = 'Discussion' THEN
                        SET @ref = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'Discussion' AND parent_id = NEW.parent_id AND is_trashed = '0');
            
                        IF @ref IS NULL THEN
                            UPDATE discussions SET last_comment_on = created_on WHERE id = NEW.parent_id;
                        ELSE
                            UPDATE discussions SET last_comment_on = @ref WHERE id = NEW.parent_id;
                        END IF;
                    ELSEIF NEW.parent_type = 'File' THEN
                        UPDATE files SET last_comment_on = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'File' AND parent_id = NEW.parent_id AND is_trashed = '0') WHERE id = NEW.parent_id;
                    ELSEIF NEW.parent_type = 'Note' THEN
                        UPDATE notes SET last_comment_on = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'Note' AND parent_id = NEW.parent_id AND is_trashed = '0') WHERE id = NEW.parent_id;
                    END IF;
                END IF;
            END");

        DB::execute("CREATE TRIGGER project_element_comment_deleted AFTER DELETE ON comments FOR EACH ROW
            BEGIN
                IF OLD.parent_type = 'Task' THEN
                    UPDATE tasks SET last_comment_on = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'Task' AND parent_id = OLD.parent_id AND is_trashed = '0') WHERE id = OLD.parent_id;
                ELSEIF OLD.parent_type = 'Discussion' THEN
                    SET @ref = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'Discussion' AND parent_id = OLD.parent_id AND is_trashed = '0');
          
                    IF @ref IS NULL THEN
                        UPDATE discussions SET last_comment_on = created_on WHERE id = OLD.parent_id;
                    ELSE
                        UPDATE discussions SET last_comment_on = @ref WHERE id = OLD.parent_id;
                    END IF;
                ELSEIF OLD.parent_type = 'File' THEN
                    UPDATE files SET last_comment_on = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'File' AND parent_id = OLD.parent_id AND is_trashed = '0') WHERE id = OLD.parent_id;
                ELSEIF OLD.parent_type = 'Note' THEN
                    UPDATE notes SET last_comment_on = (SELECT MAX(created_on) FROM comments WHERE parent_type = 'Note' AND parent_id = OLD.parent_id AND is_trashed = '0') WHERE id = OLD.parent_id;
                END IF;
            END");

        parent::loadInitialData();
    }
}
