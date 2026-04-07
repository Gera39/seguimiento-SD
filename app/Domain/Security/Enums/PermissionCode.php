<?php

namespace App\Domain\Security\Enums;

enum PermissionCode: string
{
    case USERS_MANAGE = 'users.manage';
    case ROLES_ASSIGN = 'roles.assign';
    case CATALOGS_MANAGE = 'catalogs.manage';
    case PLANS_CREATE = 'plans.create';
    case PLANS_EDIT_OWN = 'plans.edit.own';
    case PLANS_DELETE_OWN = 'plans.delete.own';
    case PLANS_SUBMIT_OWN = 'plans.submit.own';
    case PLANS_VIEW_OWN = 'plans.view.own';
    case PLANS_REVIEW_ASSIGNED = 'plans.review.assigned';
    case PLANS_FEEDBACK_CREATE = 'plans.feedback.create';
    case PLANS_AUTHORIZE = 'plans.authorize';
    case PLANS_VIEW_ALL = 'plans.view.all';
    case REPORTS_VIEW = 'reports.view';
    case WORKFLOW_OVERRIDE = 'workflow.override';
}
