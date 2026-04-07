SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

/*
    UTH - Sistema de Gestion Digital de Planeaciones Didacticas
    Base relacional inicial para SQL Server

    Objetivos del esquema:
    - Monolito MVC con Laravel 12 + Inertia/Vue
    - Seguridad con autenticacion, MFA, sesiones y RBAC
    - Catalogos academicos normalizados (3NF)
    - Maquina de estados para la planeacion didactica
    - Soporte para validaciones de horas, porcentajes y revision
*/

IF DB_ID(N'UTHPlaneaciones') IS NULL
BEGIN
    CREATE DATABASE [UTHPlaneaciones];
END;
GO

USE [UTHPlaneaciones];
GO

CREATE TABLE dbo.users (
    id BIGINT IDENTITY(1,1) NOT NULL,
    employee_number NVARCHAR(30) NULL,
    name NVARCHAR(150) NOT NULL,
    email NVARCHAR(255) NOT NULL,
    email_verified_at DATETIME2(0) NULL,
    password NVARCHAR(255) NOT NULL,
    remember_token NVARCHAR(100) NULL,
    must_change_password BIT NOT NULL CONSTRAINT DF_users_must_change_password DEFAULT (0),
    is_active BIT NOT NULL CONSTRAINT DF_users_is_active DEFAULT (1),
    last_login_at DATETIME2(0) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_users_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_users_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_users PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_users_email UNIQUE (email)
);
GO

CREATE UNIQUE INDEX UX_users_employee_number
    ON dbo.users (employee_number)
    WHERE employee_number IS NOT NULL;
GO

CREATE TABLE dbo.password_reset_tokens (
    email NVARCHAR(255) NOT NULL,
    token NVARCHAR(255) NOT NULL,
    created_at DATETIME2(0) NULL,
    CONSTRAINT PK_password_reset_tokens PRIMARY KEY CLUSTERED (email)
);
GO

CREATE TABLE dbo.sessions (
    id NVARCHAR(255) NOT NULL,
    user_id BIGINT NULL,
    ip_address NVARCHAR(45) NULL,
    user_agent NVARCHAR(500) NULL,
    payload NVARCHAR(MAX) NOT NULL,
    last_activity INT NOT NULL,
    is_mfa_verified BIT NOT NULL CONSTRAINT DF_sessions_is_mfa_verified DEFAULT (0),
    last_mfa_passed_at DATETIME2(0) NULL,
    CONSTRAINT PK_sessions PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_sessions_user_id FOREIGN KEY (user_id) REFERENCES dbo.users (id)
);
GO

CREATE INDEX IX_sessions_user_id ON dbo.sessions (user_id);
GO

CREATE INDEX IX_sessions_last_activity ON dbo.sessions (last_activity);
GO

CREATE TABLE dbo.cache (
    [key] NVARCHAR(255) NOT NULL,
    value NVARCHAR(MAX) NOT NULL,
    expiration INT NOT NULL,
    CONSTRAINT PK_cache PRIMARY KEY CLUSTERED ([key])
);
GO

CREATE TABLE dbo.cache_locks (
    [key] NVARCHAR(255) NOT NULL,
    owner NVARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    CONSTRAINT PK_cache_locks PRIMARY KEY CLUSTERED ([key])
);
GO

CREATE TABLE dbo.jobs (
    id BIGINT IDENTITY(1,1) NOT NULL,
    queue NVARCHAR(255) NOT NULL,
    payload NVARCHAR(MAX) NOT NULL,
    attempts TINYINT NOT NULL,
    reserved_at INT NULL,
    available_at INT NOT NULL,
    created_at INT NOT NULL,
    CONSTRAINT PK_jobs PRIMARY KEY CLUSTERED (id)
);
GO

CREATE INDEX IX_jobs_queue ON dbo.jobs (queue);
GO

CREATE INDEX IX_jobs_reserved_at ON dbo.jobs (reserved_at);
GO

CREATE TABLE dbo.job_batches (
    id NVARCHAR(255) NOT NULL,
    name NVARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids NVARCHAR(MAX) NOT NULL,
    options NVARCHAR(MAX) NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL,
    CONSTRAINT PK_job_batches PRIMARY KEY CLUSTERED (id)
);
GO

CREATE TABLE dbo.failed_jobs (
    id BIGINT IDENTITY(1,1) NOT NULL,
    uuid NVARCHAR(255) NOT NULL,
    connection NVARCHAR(255) NOT NULL,
    queue NVARCHAR(255) NOT NULL,
    payload NVARCHAR(MAX) NOT NULL,
    exception NVARCHAR(MAX) NOT NULL,
    failed_at DATETIME2(0) NOT NULL CONSTRAINT DF_failed_jobs_failed_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_failed_jobs PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_failed_jobs_uuid UNIQUE (uuid)
);
GO

CREATE TABLE dbo.roles (
    id INT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(30) NOT NULL,
    name NVARCHAR(100) NOT NULL,
    description NVARCHAR(255) NULL,
    is_system BIT NOT NULL CONSTRAINT DF_roles_is_system DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_roles_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_roles_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_roles PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_roles_code UNIQUE (code),
    CONSTRAINT UQ_roles_name UNIQUE (name)
);
GO

CREATE TABLE dbo.permissions (
    id INT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(60) NOT NULL,
    name NVARCHAR(120) NOT NULL,
    description NVARCHAR(255) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_permissions_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_permissions_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_permissions PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_permissions_code UNIQUE (code),
    CONSTRAINT UQ_permissions_name UNIQUE (name)
);
GO

CREATE TABLE dbo.role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_role_permissions_created_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_role_permissions PRIMARY KEY CLUSTERED (role_id, permission_id),
    CONSTRAINT FK_role_permissions_role_id FOREIGN KEY (role_id) REFERENCES dbo.roles (id),
    CONSTRAINT FK_role_permissions_permission_id FOREIGN KEY (permission_id) REFERENCES dbo.permissions (id)
);
GO

CREATE TABLE dbo.user_mfa_methods (
    id BIGINT IDENTITY(1,1) NOT NULL,
    user_id BIGINT NOT NULL,
    method_type NVARCHAR(20) NOT NULL,
    label NVARCHAR(100) NOT NULL,
    secret_encrypted NVARCHAR(500) NULL,
    destination_masked NVARCHAR(120) NULL,
    is_primary BIT NOT NULL CONSTRAINT DF_user_mfa_methods_is_primary DEFAULT (0),
    confirmed_at DATETIME2(0) NULL,
    last_used_at DATETIME2(0) NULL,
    is_active BIT NOT NULL CONSTRAINT DF_user_mfa_methods_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_mfa_methods_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_mfa_methods_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_user_mfa_methods PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_user_mfa_methods_user_id FOREIGN KEY (user_id) REFERENCES dbo.users (id),
    CONSTRAINT CHK_user_mfa_methods_method_type CHECK (method_type IN (N'TOTP', N'EMAIL_OTP'))
);
GO

CREATE UNIQUE INDEX UX_user_mfa_methods_primary
    ON dbo.user_mfa_methods (user_id)
    WHERE is_primary = 1 AND is_active = 1;
GO

CREATE TABLE dbo.user_mfa_recovery_codes (
    id BIGINT IDENTITY(1,1) NOT NULL,
    mfa_method_id BIGINT NOT NULL,
    code_hash NVARCHAR(255) NOT NULL,
    used_at DATETIME2(0) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_mfa_recovery_codes_created_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_user_mfa_recovery_codes PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_user_mfa_recovery_codes_mfa_method_id FOREIGN KEY (mfa_method_id) REFERENCES dbo.user_mfa_methods (id)
);
GO

CREATE INDEX IX_user_mfa_recovery_codes_method_id ON dbo.user_mfa_recovery_codes (mfa_method_id);
GO

CREATE TABLE dbo.auth_login_audits (
    id BIGINT IDENTITY(1,1) NOT NULL,
    user_id BIGINT NULL,
    session_id NVARCHAR(255) NULL,
    mfa_method_id BIGINT NULL,
    event_type NVARCHAR(30) NOT NULL,
    is_success BIT NOT NULL,
    failure_reason NVARCHAR(250) NULL,
    ip_address NVARCHAR(45) NULL,
    user_agent NVARCHAR(500) NULL,
    occurred_at DATETIME2(0) NOT NULL CONSTRAINT DF_auth_login_audits_occurred_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_auth_login_audits PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_auth_login_audits_user_id FOREIGN KEY (user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_auth_login_audits_mfa_method_id FOREIGN KEY (mfa_method_id) REFERENCES dbo.user_mfa_methods (id),
    CONSTRAINT CHK_auth_login_audits_event_type CHECK (
        event_type IN (N'LOGIN_SUCCESS', N'LOGIN_FAILED', N'MFA_SUCCESS', N'MFA_FAILED', N'LOGOUT')
    )
);
GO

CREATE INDEX IX_auth_login_audits_user_id_occurred_at
    ON dbo.auth_login_audits (user_id, occurred_at DESC);
GO

CREATE TABLE dbo.careers (
    id BIGINT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(20) NOT NULL,
    name NVARCHAR(200) NOT NULL,
    short_name NVARCHAR(80) NULL,
    educational_level NVARCHAR(50) NOT NULL,
    duration_terms TINYINT NOT NULL,
    is_active BIT NOT NULL CONSTRAINT DF_careers_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_careers_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_careers_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_careers PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_careers_code UNIQUE (code),
    CONSTRAINT UQ_careers_name UNIQUE (name)
);
GO

CREATE TABLE dbo.subjects (
    id BIGINT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(20) NOT NULL,
    name NVARCHAR(200) NOT NULL,
    subject_type NVARCHAR(30) NOT NULL CONSTRAINT DF_subjects_subject_type DEFAULT (N'REGULAR'),
    default_total_hours DECIMAL(6,2) NULL,
    default_theoretical_hours DECIMAL(6,2) NULL,
    default_practical_hours DECIMAL(6,2) NULL,
    credits DECIMAL(5,2) NULL,
    is_active BIT NOT NULL CONSTRAINT DF_subjects_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_subjects_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_subjects_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_subjects PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_subjects_code UNIQUE (code),
    CONSTRAINT UQ_subjects_name UNIQUE (name),
    CONSTRAINT CHK_subjects_hour_defaults CHECK (
        default_total_hours IS NULL
        OR (
            ISNULL(default_theoretical_hours, 0) >= 0
            AND ISNULL(default_practical_hours, 0) >= 0
            AND default_total_hours = ISNULL(default_theoretical_hours, 0) + ISNULL(default_practical_hours, 0)
        )
    )
);
GO

CREATE TABLE dbo.academic_periods (
    id BIGINT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(20) NOT NULL,
    name NVARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status_code NVARCHAR(20) NOT NULL CONSTRAINT DF_academic_periods_status_code DEFAULT (N'PLANNED'),
    is_active BIT NOT NULL CONSTRAINT DF_academic_periods_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_academic_periods_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_academic_periods_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_academic_periods PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_academic_periods_code UNIQUE (code),
    CONSTRAINT CHK_academic_periods_date_range CHECK (end_date >= start_date),
    CONSTRAINT CHK_academic_periods_status_code CHECK (
        status_code IN (N'PLANNED', N'ACTIVE', N'CLOSED')
    )
);
GO

CREATE TABLE dbo.[groups] (
    id BIGINT IDENTITY(1,1) NOT NULL,
    career_id BIGINT NOT NULL,
    academic_period_id BIGINT NOT NULL,
    group_code NVARCHAR(20) NOT NULL,
    shift_code NVARCHAR(20) NOT NULL,
    term_number TINYINT NOT NULL,
    is_active BIT NOT NULL CONSTRAINT DF_groups_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_groups_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_groups_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_groups PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_groups_career_id FOREIGN KEY (career_id) REFERENCES dbo.careers (id),
    CONSTRAINT FK_groups_academic_period_id FOREIGN KEY (academic_period_id) REFERENCES dbo.academic_periods (id),
    CONSTRAINT UQ_groups_scope UNIQUE (career_id, academic_period_id, group_code),
    CONSTRAINT CHK_groups_shift_code CHECK (shift_code IN (N'MORNING', N'EVENING', N'MIXED')),
    CONSTRAINT CHK_groups_term_number CHECK (term_number >= 1)
);
GO

CREATE TABLE dbo.career_subjects (
    id BIGINT IDENTITY(1,1) NOT NULL,
    career_id BIGINT NOT NULL,
    subject_id BIGINT NOT NULL,
    term_number TINYINT NOT NULL,
    curricular_block NVARCHAR(100) NULL,
    total_hours DECIMAL(6,2) NOT NULL,
    theoretical_hours DECIMAL(6,2) NOT NULL CONSTRAINT DF_career_subjects_theoretical_hours DEFAULT (0),
    practical_hours DECIMAL(6,2) NOT NULL CONSTRAINT DF_career_subjects_practical_hours DEFAULT (0),
    credits DECIMAL(5,2) NULL,
    is_active BIT NOT NULL CONSTRAINT DF_career_subjects_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_career_subjects_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_career_subjects_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_career_subjects PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_career_subjects_career_id FOREIGN KEY (career_id) REFERENCES dbo.careers (id),
    CONSTRAINT FK_career_subjects_subject_id FOREIGN KEY (subject_id) REFERENCES dbo.subjects (id),
    CONSTRAINT UQ_career_subjects_curriculum UNIQUE (career_id, subject_id, term_number),
    CONSTRAINT CHK_career_subjects_hours CHECK (
        total_hours > 0
        AND theoretical_hours >= 0
        AND practical_hours >= 0
        AND total_hours = theoretical_hours + practical_hours
    )
);
GO

CREATE TABLE dbo.group_subject_offerings (
    id BIGINT IDENTITY(1,1) NOT NULL,
    group_id BIGINT NOT NULL,
    career_subject_id BIGINT NOT NULL,
    modality_code NVARCHAR(20) NOT NULL CONSTRAINT DF_group_subject_offerings_modality_code DEFAULT (N'PRESENTIAL'),
    scheduled_start_date DATE NULL,
    scheduled_end_date DATE NULL,
    is_active BIT NOT NULL CONSTRAINT DF_group_subject_offerings_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_group_subject_offerings_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_group_subject_offerings_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_group_subject_offerings PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_group_subject_offerings_group_id FOREIGN KEY (group_id) REFERENCES dbo.[groups] (id),
    CONSTRAINT FK_group_subject_offerings_career_subject_id FOREIGN KEY (career_subject_id) REFERENCES dbo.career_subjects (id),
    CONSTRAINT UQ_group_subject_offerings_scope UNIQUE (group_id, career_subject_id),
    CONSTRAINT CHK_group_subject_offerings_modality_code CHECK (
        modality_code IN (N'PRESENTIAL', N'VIRTUAL', N'HYBRID')
    ),
    CONSTRAINT CHK_group_subject_offerings_date_range CHECK (
        scheduled_end_date IS NULL
        OR scheduled_start_date IS NULL
        OR scheduled_end_date >= scheduled_start_date
    )
);
GO

CREATE TABLE dbo.teacher_subject_assignments (
    id BIGINT IDENTITY(1,1) NOT NULL,
    group_subject_offering_id BIGINT NOT NULL,
    teacher_user_id BIGINT NOT NULL,
    assigned_by_user_id BIGINT NULL,
    assignment_role_code NVARCHAR(20) NOT NULL CONSTRAINT DF_teacher_subject_assignments_assignment_role_code DEFAULT (N'PRIMARY'),
    assignment_status_code NVARCHAR(20) NOT NULL CONSTRAINT DF_teacher_subject_assignments_assignment_status_code DEFAULT (N'ACTIVE'),
    assigned_at DATETIME2(0) NOT NULL CONSTRAINT DF_teacher_subject_assignments_assigned_at DEFAULT (SYSUTCDATETIME()),
    released_at DATETIME2(0) NULL,
    is_active BIT NOT NULL CONSTRAINT DF_teacher_subject_assignments_is_active DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_teacher_subject_assignments_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_teacher_subject_assignments_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_teacher_subject_assignments PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_teacher_subject_assignments_group_subject_offering_id FOREIGN KEY (group_subject_offering_id) REFERENCES dbo.group_subject_offerings (id),
    CONSTRAINT FK_teacher_subject_assignments_teacher_user_id FOREIGN KEY (teacher_user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_teacher_subject_assignments_assigned_by_user_id FOREIGN KEY (assigned_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT UQ_teacher_subject_assignments_scope UNIQUE (group_subject_offering_id, teacher_user_id),
    CONSTRAINT CHK_teacher_subject_assignments_assignment_role_code CHECK (
        assignment_role_code IN (N'PRIMARY', N'ASSISTANT')
    ),
    CONSTRAINT CHK_teacher_subject_assignments_assignment_status_code CHECK (
        assignment_status_code IN (N'ACTIVE', N'RELEASED')
    )
);
GO

CREATE INDEX IX_teacher_subject_assignments_teacher_user_id
    ON dbo.teacher_subject_assignments (teacher_user_id, is_active);
GO

CREATE TABLE dbo.user_role_assignments (
    id BIGINT IDENTITY(1,1) NOT NULL,
    user_id BIGINT NOT NULL,
    role_id INT NOT NULL,
    career_id BIGINT NULL,
    valid_from DATE NULL,
    valid_to DATE NULL,
    is_active BIT NOT NULL CONSTRAINT DF_user_role_assignments_is_active DEFAULT (1),
    assigned_by_user_id BIGINT NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_role_assignments_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_user_role_assignments_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_user_role_assignments PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_user_role_assignments_user_id FOREIGN KEY (user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_user_role_assignments_role_id FOREIGN KEY (role_id) REFERENCES dbo.roles (id),
    CONSTRAINT FK_user_role_assignments_career_id FOREIGN KEY (career_id) REFERENCES dbo.careers (id),
    CONSTRAINT FK_user_role_assignments_assigned_by_user_id FOREIGN KEY (assigned_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT CHK_user_role_assignments_date_range CHECK (
        valid_to IS NULL
        OR valid_from IS NULL
        OR valid_to >= valid_from
    )
);
GO

CREATE INDEX IX_user_role_assignments_user_id_active
    ON dbo.user_role_assignments (user_id, is_active);
GO

CREATE INDEX IX_user_role_assignments_role_id_career_id
    ON dbo.user_role_assignments (role_id, career_id, is_active);
GO

CREATE TABLE dbo.planning_statuses (
    id INT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(30) NOT NULL,
    name NVARCHAR(100) NOT NULL,
    is_teacher_editable BIT NOT NULL,
    is_teacher_deletable BIT NOT NULL,
    is_terminal BIT NOT NULL CONSTRAINT DF_planning_statuses_is_terminal DEFAULT (0),
    sort_order TINYINT NOT NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_planning_statuses_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_planning_statuses_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_planning_statuses PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_planning_statuses_code UNIQUE (code),
    CONSTRAINT UQ_planning_statuses_name UNIQUE (name)
);
GO

CREATE TABLE dbo.evaluation_criterion_types (
    id INT IDENTITY(1,1) NOT NULL,
    code NVARCHAR(30) NOT NULL,
    name NVARCHAR(100) NOT NULL,
    description NVARCHAR(255) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_evaluation_criterion_types_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_evaluation_criterion_types_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_evaluation_criterion_types PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_evaluation_criterion_types_code UNIQUE (code),
    CONSTRAINT UQ_evaluation_criterion_types_name UNIQUE (name)
);
GO

CREATE TABLE dbo.planning_transition_rules (
    id INT IDENTITY(1,1) NOT NULL,
    from_status_id INT NOT NULL,
    to_status_id INT NOT NULL,
    triggered_by_role_id INT NOT NULL,
    transition_code NVARCHAR(60) NOT NULL,
    requires_comment BIT NOT NULL CONSTRAINT DF_planning_transition_rules_requires_comment DEFAULT (0),
    reopens_for_editing BIT NOT NULL CONSTRAINT DF_planning_transition_rules_reopens_for_editing DEFAULT (0),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_planning_transition_rules_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_planning_transition_rules_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_planning_transition_rules PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_planning_transition_rules_from_status_id FOREIGN KEY (from_status_id) REFERENCES dbo.planning_statuses (id),
    CONSTRAINT FK_planning_transition_rules_to_status_id FOREIGN KEY (to_status_id) REFERENCES dbo.planning_statuses (id),
    CONSTRAINT FK_planning_transition_rules_triggered_by_role_id FOREIGN KEY (triggered_by_role_id) REFERENCES dbo.roles (id),
    CONSTRAINT UQ_planning_transition_rules_scope UNIQUE (from_status_id, to_status_id, triggered_by_role_id),
    CONSTRAINT UQ_planning_transition_rules_transition_code UNIQUE (transition_code)
);
GO

CREATE TABLE dbo.didactic_plans (
    id BIGINT IDENTITY(1,1) NOT NULL,
    public_uuid UNIQUEIDENTIFIER NOT NULL CONSTRAINT DF_didactic_plans_public_uuid DEFAULT (NEWSEQUENTIALID()),
    plan_folio NVARCHAR(30) NOT NULL,
    teacher_subject_assignment_id BIGINT NOT NULL,
    status_id INT NOT NULL,
    version_number INT NOT NULL CONSTRAINT DF_didactic_plans_version_number DEFAULT (1),
    current_review_round SMALLINT NOT NULL CONSTRAINT DF_didactic_plans_current_review_round DEFAULT (0),
    general_objective NVARCHAR(1000) NOT NULL,
    course_intent NVARCHAR(1000) NULL,
    methodology_notes NVARCHAR(1000) NULL,
    general_observations NVARCHAR(1000) NULL,
    submission_notes NVARCHAR(500) NULL,
    submitted_at DATETIME2(0) NULL,
    locked_at DATETIME2(0) NULL,
    feedback_released_at DATETIME2(0) NULL,
    authorized_at DATETIME2(0) NULL,
    authorized_by_user_id BIGINT NULL,
    created_by_user_id BIGINT NOT NULL,
    updated_by_user_id BIGINT NOT NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plans_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plans_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_didactic_plans PRIMARY KEY CLUSTERED (id),
    CONSTRAINT UQ_didactic_plans_public_uuid UNIQUE (public_uuid),
    CONSTRAINT UQ_didactic_plans_plan_folio UNIQUE (plan_folio),
    CONSTRAINT FK_didactic_plans_teacher_subject_assignment_id FOREIGN KEY (teacher_subject_assignment_id) REFERENCES dbo.teacher_subject_assignments (id),
    CONSTRAINT FK_didactic_plans_status_id FOREIGN KEY (status_id) REFERENCES dbo.planning_statuses (id),
    CONSTRAINT FK_didactic_plans_authorized_by_user_id FOREIGN KEY (authorized_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_didactic_plans_created_by_user_id FOREIGN KEY (created_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_didactic_plans_updated_by_user_id FOREIGN KEY (updated_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT CHK_didactic_plans_version_number CHECK (version_number >= 1),
    CONSTRAINT CHK_didactic_plans_current_review_round CHECK (current_review_round >= 0)
);
GO

CREATE INDEX IX_didactic_plans_assignment_status
    ON dbo.didactic_plans (teacher_subject_assignment_id, status_id);
GO

CREATE INDEX IX_didactic_plans_status_created_at
    ON dbo.didactic_plans (status_id, created_at DESC);
GO

CREATE TABLE dbo.didactic_plan_references (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_id BIGINT NOT NULL,
    reference_type NVARCHAR(20) NOT NULL,
    citation NVARCHAR(1000) NOT NULL,
    sort_order INT NOT NULL CONSTRAINT DF_didactic_plan_references_sort_order DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_references_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_references_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_didactic_plan_references PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_references_didactic_plan_id FOREIGN KEY (didactic_plan_id) REFERENCES dbo.didactic_plans (id) ON DELETE CASCADE,
    CONSTRAINT CHK_didactic_plan_references_reference_type CHECK (
        reference_type IN (N'BIBLIOGRAPHY', N'WEBGRAPHY', N'RESOURCE')
    ),
    CONSTRAINT CHK_didactic_plan_references_sort_order CHECK (sort_order >= 1)
);
GO

CREATE INDEX IX_didactic_plan_references_didactic_plan_id
    ON dbo.didactic_plan_references (didactic_plan_id, sort_order);
GO

CREATE TABLE dbo.didactic_plan_units (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_id BIGINT NOT NULL,
    unit_number TINYINT NOT NULL,
    title NVARCHAR(200) NOT NULL,
    learning_objective NVARCHAR(1000) NOT NULL,
    planned_hours DECIMAL(6,2) NOT NULL,
    progress_percentage DECIMAL(5,2) NOT NULL,
    start_week TINYINT NULL,
    end_week TINYINT NULL,
    teaching_strategy NVARCHAR(1000) NULL,
    learning_evidence NVARCHAR(1000) NULL,
    assessment_strategy NVARCHAR(1000) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_units_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_units_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_didactic_plan_units PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_units_didactic_plan_id FOREIGN KEY (didactic_plan_id) REFERENCES dbo.didactic_plans (id) ON DELETE CASCADE,
    CONSTRAINT UQ_didactic_plan_units_scope UNIQUE (didactic_plan_id, unit_number),
    CONSTRAINT CHK_didactic_plan_units_planned_hours CHECK (planned_hours > 0),
    CONSTRAINT CHK_didactic_plan_units_progress_percentage CHECK (progress_percentage >= 0 AND progress_percentage <= 100),
    CONSTRAINT CHK_didactic_plan_units_week_range CHECK (
        end_week IS NULL
        OR start_week IS NULL
        OR end_week >= start_week
    )
);
GO

CREATE INDEX IX_didactic_plan_units_didactic_plan_id
    ON dbo.didactic_plan_units (didactic_plan_id, unit_number);
GO

CREATE TABLE dbo.didactic_plan_modules (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_unit_id BIGINT NOT NULL,
    module_number SMALLINT NOT NULL,
    title NVARCHAR(200) NOT NULL,
    topic_description NVARCHAR(1000) NOT NULL,
    theoretical_hours DECIMAL(6,2) NOT NULL CONSTRAINT DF_didactic_plan_modules_theoretical_hours DEFAULT (0),
    practical_hours DECIMAL(6,2) NOT NULL CONSTRAINT DF_didactic_plan_modules_practical_hours DEFAULT (0),
    planned_hours AS CONVERT(DECIMAL(6,2), theoretical_hours + practical_hours) PERSISTED,
    learning_activity NVARCHAR(1000) NULL,
    teaching_activity NVARCHAR(1000) NULL,
    resources NVARCHAR(1000) NULL,
    assessment_activity NVARCHAR(1000) NULL,
    delivery_mode NVARCHAR(20) NOT NULL CONSTRAINT DF_didactic_plan_modules_delivery_mode DEFAULT (N'PRESENTIAL'),
    scheduled_date DATE NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_modules_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_modules_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_didactic_plan_modules PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_modules_didactic_plan_unit_id FOREIGN KEY (didactic_plan_unit_id) REFERENCES dbo.didactic_plan_units (id) ON DELETE CASCADE,
    CONSTRAINT UQ_didactic_plan_modules_scope UNIQUE (didactic_plan_unit_id, module_number),
    CONSTRAINT CHK_didactic_plan_modules_hours CHECK (
        theoretical_hours >= 0
        AND practical_hours >= 0
        AND theoretical_hours + practical_hours > 0
    ),
    CONSTRAINT CHK_didactic_plan_modules_delivery_mode CHECK (
        delivery_mode IN (N'PRESENTIAL', N'VIRTUAL', N'HYBRID')
    ),
    CONSTRAINT CHK_didactic_plan_modules_module_number CHECK (module_number >= 1)
);
GO

CREATE INDEX IX_didactic_plan_modules_unit_id
    ON dbo.didactic_plan_modules (didactic_plan_unit_id, module_number);
GO

CREATE TABLE dbo.didactic_plan_evaluation_criteria (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_id BIGINT NOT NULL,
    didactic_plan_unit_id BIGINT NULL,
    criterion_type_id INT NOT NULL,
    criterion_name NVARCHAR(150) NOT NULL,
    description NVARCHAR(500) NOT NULL,
    evidence_description NVARCHAR(500) NOT NULL,
    instrument_description NVARCHAR(250) NOT NULL,
    weight_percentage DECIMAL(5,2) NOT NULL,
    minimum_score DECIMAL(5,2) NULL,
    sort_order INT NOT NULL CONSTRAINT DF_didactic_plan_evaluation_criteria_sort_order DEFAULT (1),
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_evaluation_criteria_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_evaluation_criteria_updated_at DEFAULT (SYSUTCDATETIME()),
    row_version ROWVERSION NOT NULL,
    CONSTRAINT PK_didactic_plan_evaluation_criteria PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_evaluation_criteria_didactic_plan_id FOREIGN KEY (didactic_plan_id) REFERENCES dbo.didactic_plans (id) ON DELETE CASCADE,
    CONSTRAINT FK_didactic_plan_evaluation_criteria_didactic_plan_unit_id FOREIGN KEY (didactic_plan_unit_id) REFERENCES dbo.didactic_plan_units (id),
    CONSTRAINT FK_didactic_plan_evaluation_criteria_criterion_type_id FOREIGN KEY (criterion_type_id) REFERENCES dbo.evaluation_criterion_types (id),
    CONSTRAINT CHK_didactic_plan_evaluation_criteria_weight_percentage CHECK (weight_percentage > 0 AND weight_percentage <= 100),
    CONSTRAINT CHK_didactic_plan_evaluation_criteria_minimum_score CHECK (
        minimum_score IS NULL OR (minimum_score >= 0 AND minimum_score <= 100)
    ),
    CONSTRAINT CHK_didactic_plan_evaluation_criteria_sort_order CHECK (sort_order >= 1)
);
GO

CREATE INDEX IX_didactic_plan_evaluation_criteria_plan_id
    ON dbo.didactic_plan_evaluation_criteria (didactic_plan_id, sort_order);
GO

CREATE INDEX IX_didactic_plan_evaluation_criteria_unit_id
    ON dbo.didactic_plan_evaluation_criteria (didactic_plan_unit_id);
GO

CREATE TABLE dbo.didactic_plan_reviews (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_id BIGINT NOT NULL,
    review_round SMALLINT NOT NULL,
    review_stage_code NVARCHAR(20) NOT NULL,
    reviewer_user_id BIGINT NOT NULL,
    assigned_by_user_id BIGINT NULL,
    decision_status_id INT NULL,
    general_comments NVARCHAR(1500) NULL,
    started_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_reviews_started_at DEFAULT (SYSUTCDATETIME()),
    reviewed_at DATETIME2(0) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_reviews_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_reviews_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_didactic_plan_reviews PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_reviews_didactic_plan_id FOREIGN KEY (didactic_plan_id) REFERENCES dbo.didactic_plans (id) ON DELETE CASCADE,
    CONSTRAINT FK_didactic_plan_reviews_reviewer_user_id FOREIGN KEY (reviewer_user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_didactic_plan_reviews_assigned_by_user_id FOREIGN KEY (assigned_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT FK_didactic_plan_reviews_decision_status_id FOREIGN KEY (decision_status_id) REFERENCES dbo.planning_statuses (id),
    CONSTRAINT UQ_didactic_plan_reviews_scope UNIQUE (didactic_plan_id, review_round, review_stage_code),
    CONSTRAINT CHK_didactic_plan_reviews_review_round CHECK (review_round >= 1),
    CONSTRAINT CHK_didactic_plan_reviews_review_stage_code CHECK (
        review_stage_code IN (N'TECHNICAL', N'FINAL')
    )
);
GO

CREATE INDEX IX_didactic_plan_reviews_reviewer_user_id
    ON dbo.didactic_plan_reviews (reviewer_user_id, started_at DESC);
GO

CREATE TABLE dbo.didactic_plan_review_comments (
    id BIGINT IDENTITY(1,1) NOT NULL,
    review_id BIGINT NOT NULL,
    entity_type NVARCHAR(20) NOT NULL,
    entity_id BIGINT NULL,
    severity_code NVARCHAR(20) NOT NULL,
    comment_text NVARCHAR(1000) NOT NULL,
    is_resolved BIT NOT NULL CONSTRAINT DF_didactic_plan_review_comments_is_resolved DEFAULT (0),
    resolved_by_user_id BIGINT NULL,
    resolved_at DATETIME2(0) NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_review_comments_created_at DEFAULT (SYSUTCDATETIME()),
    updated_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_review_comments_updated_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_didactic_plan_review_comments PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_review_comments_review_id FOREIGN KEY (review_id) REFERENCES dbo.didactic_plan_reviews (id) ON DELETE CASCADE,
    CONSTRAINT FK_didactic_plan_review_comments_resolved_by_user_id FOREIGN KEY (resolved_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT CHK_didactic_plan_review_comments_entity_type CHECK (
        entity_type IN (N'PLAN', N'UNIT', N'MODULE', N'EVALUATION')
    ),
    CONSTRAINT CHK_didactic_plan_review_comments_severity_code CHECK (
        severity_code IN (N'INFO', N'WARNING', N'REQUIRED')
    )
);
GO

CREATE INDEX IX_didactic_plan_review_comments_review_id
    ON dbo.didactic_plan_review_comments (review_id, severity_code, is_resolved);
GO

CREATE TABLE dbo.didactic_plan_status_history (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_id BIGINT NOT NULL,
    from_status_id INT NULL,
    to_status_id INT NOT NULL,
    transition_rule_id INT NULL,
    changed_by_user_id BIGINT NOT NULL,
    comments NVARCHAR(500) NULL,
    changed_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_status_history_changed_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_didactic_plan_status_history PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_status_history_didactic_plan_id FOREIGN KEY (didactic_plan_id) REFERENCES dbo.didactic_plans (id) ON DELETE CASCADE,
    CONSTRAINT FK_didactic_plan_status_history_from_status_id FOREIGN KEY (from_status_id) REFERENCES dbo.planning_statuses (id),
    CONSTRAINT FK_didactic_plan_status_history_to_status_id FOREIGN KEY (to_status_id) REFERENCES dbo.planning_statuses (id),
    CONSTRAINT FK_didactic_plan_status_history_transition_rule_id FOREIGN KEY (transition_rule_id) REFERENCES dbo.planning_transition_rules (id),
    CONSTRAINT FK_didactic_plan_status_history_changed_by_user_id FOREIGN KEY (changed_by_user_id) REFERENCES dbo.users (id)
);
GO

CREATE INDEX IX_didactic_plan_status_history_plan_id
    ON dbo.didactic_plan_status_history (didactic_plan_id, changed_at DESC);
GO

CREATE TABLE dbo.didactic_plan_validation_snapshots (
    id BIGINT IDENTITY(1,1) NOT NULL,
    didactic_plan_id BIGINT NOT NULL,
    validation_context NVARCHAR(30) NOT NULL,
    total_units SMALLINT NOT NULL,
    total_modules INT NOT NULL,
    total_unit_hours DECIMAL(8,2) NOT NULL,
    total_module_hours DECIMAL(8,2) NOT NULL,
    total_progress_percentage DECIMAL(6,2) NOT NULL,
    total_evaluation_percentage DECIMAL(6,2) NOT NULL,
    is_valid BIT NOT NULL,
    validation_details NVARCHAR(MAX) NULL,
    created_by_user_id BIGINT NOT NULL,
    created_at DATETIME2(0) NOT NULL CONSTRAINT DF_didactic_plan_validation_snapshots_created_at DEFAULT (SYSUTCDATETIME()),
    CONSTRAINT PK_didactic_plan_validation_snapshots PRIMARY KEY CLUSTERED (id),
    CONSTRAINT FK_didactic_plan_validation_snapshots_didactic_plan_id FOREIGN KEY (didactic_plan_id) REFERENCES dbo.didactic_plans (id) ON DELETE CASCADE,
    CONSTRAINT FK_didactic_plan_validation_snapshots_created_by_user_id FOREIGN KEY (created_by_user_id) REFERENCES dbo.users (id),
    CONSTRAINT CHK_didactic_plan_validation_snapshots_validation_context CHECK (
        validation_context IN (N'DRAFT_SAVE', N'SUBMISSION', N'AUTHORIZATION')
    )
);
GO

CREATE INDEX IX_didactic_plan_validation_snapshots_plan_id
    ON dbo.didactic_plan_validation_snapshots (didactic_plan_id, created_at DESC);
GO

INSERT INTO dbo.roles (code, name, description)
VALUES
    (N'ADMIN', N'Administrador', N'Control total del sistema, catalogos, seguridad y auditoria'),
    (N'DIRECTIVO', N'Directivo', N'Autorizacion final y consulta ejecutiva'),
    (N'DOCENTE', N'Docente', N'Creacion y envio de planeaciones propias'),
    (N'REVISOR', N'Revisor', N'Revision tecnica y retroalimentacion');
GO

INSERT INTO dbo.permissions (code, name, description)
VALUES
    (N'users.manage', N'Gestionar usuarios', N'Altas, bajas logicas y actualizacion de usuarios'),
    (N'roles.assign', N'Asignar roles', N'Asignacion de roles y alcance academico'),
    (N'catalogs.manage', N'Gestionar catalogos', N'Carreras, asignaturas, periodos y grupos'),
    (N'plans.create', N'Crear planeaciones', N'Crear planeaciones didacticas'),
    (N'plans.edit.own', N'Editar planeaciones propias', N'Editar planeaciones en estado editable'),
    (N'plans.delete.own', N'Eliminar planeaciones propias', N'Eliminar planeaciones solo si siguen en borrador'),
    (N'plans.submit.own', N'Enviar planeaciones propias', N'Solicitar revision de planeaciones propias'),
    (N'plans.view.own', N'Consultar planeaciones propias', N'Consultar planeaciones creadas por el docente'),
    (N'plans.review.assigned', N'Revisar planeaciones asignadas', N'Revision tecnica de planeaciones'),
    (N'plans.feedback.create', N'Emitir retroalimentacion', N'Crear comentarios y observaciones de revision'),
    (N'plans.authorize', N'Autorizar planeaciones', N'Autorizacion final de planeaciones'),
    (N'plans.view.all', N'Consultar todas las planeaciones', N'Consulta institucional de planeaciones'),
    (N'reports.view', N'Consultar reportes', N'Acceso a reportes ejecutivos y operativos'),
    (N'workflow.override', N'Sobrescribir workflow', N'Uso exclusivo de administracion para contingencias');
GO

INSERT INTO dbo.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.roles AS r
CROSS JOIN dbo.permissions AS p
WHERE r.code = N'ADMIN';
GO

INSERT INTO dbo.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.roles AS r
INNER JOIN dbo.permissions AS p
    ON p.code IN (N'plans.authorize', N'plans.view.all', N'reports.view')
WHERE r.code = N'DIRECTIVO';
GO

INSERT INTO dbo.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.roles AS r
INNER JOIN dbo.permissions AS p
    ON p.code IN (
        N'plans.create',
        N'plans.edit.own',
        N'plans.delete.own',
        N'plans.submit.own',
        N'plans.view.own'
    )
WHERE r.code = N'DOCENTE';
GO

INSERT INTO dbo.role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.roles AS r
INNER JOIN dbo.permissions AS p
    ON p.code IN (
        N'plans.review.assigned',
        N'plans.feedback.create',
        N'plans.view.all',
        N'reports.view'
    )
WHERE r.code = N'REVISOR';
GO

INSERT INTO dbo.planning_statuses (code, name, is_teacher_editable, is_teacher_deletable, is_terminal, sort_order)
VALUES
    (N'DRAFT', N'Borrador', 1, 1, 0, 1),
    (N'SUBMITTED', N'Solicitada para revision', 0, 0, 0, 2),
    (N'UNDER_REVIEW', N'En revision', 0, 0, 0, 3),
    (N'FEEDBACK', N'Retroalimentacion emitida', 1, 0, 0, 4),
    (N'AUTHORIZED', N'Autorizada', 0, 0, 1, 5),
    (N'REJECTED', N'Rechazada', 0, 0, 1, 6);
GO

INSERT INTO dbo.evaluation_criterion_types (code, name, description)
VALUES
    (N'DIAGNOSTIC', N'Diagnostico', N'Evaluacion de inicio para medir conocimientos previos'),
    (N'FORMATIVE', N'Formativa', N'Seguimiento y evidencias de avance durante el curso'),
    (N'SUMMATIVE', N'Sumativa', N'Evaluacion final y acreditacion');
GO

INSERT INTO dbo.planning_transition_rules (
    from_status_id,
    to_status_id,
    triggered_by_role_id,
    transition_code,
    requires_comment,
    reopens_for_editing
)
SELECT s_from.id, s_to.id, r.id, v.transition_code, v.requires_comment, v.reopens_for_editing
FROM (
    VALUES
        (N'DRAFT', N'SUBMITTED', N'DOCENTE', N'DOCENTE_SUBMIT', 0, 0),
        (N'FEEDBACK', N'SUBMITTED', N'DOCENTE', N'DOCENTE_RESUBMIT', 0, 0),
        (N'SUBMITTED', N'UNDER_REVIEW', N'REVISOR', N'REVISOR_TAKE', 0, 0),
        (N'UNDER_REVIEW', N'FEEDBACK', N'REVISOR', N'REVISOR_FEEDBACK', 1, 1),
        (N'UNDER_REVIEW', N'AUTHORIZED', N'DIRECTIVO', N'DIRECTIVO_AUTHORIZE', 0, 0),
        (N'UNDER_REVIEW', N'REJECTED', N'DIRECTIVO', N'DIRECTIVO_REJECT', 1, 0)
) AS v(from_code, to_code, role_code, transition_code, requires_comment, reopens_for_editing)
INNER JOIN dbo.planning_statuses AS s_from
    ON s_from.code = v.from_code
INNER JOIN dbo.planning_statuses AS s_to
    ON s_to.code = v.to_code
INNER JOIN dbo.roles AS r
    ON r.code = v.role_code;
GO

CREATE VIEW dbo.vw_didactic_plan_totals
AS
SELECT
    p.id AS didactic_plan_id,
    ISNULL(unit_summary.total_units, 0) AS total_units,
    ISNULL(unit_summary.total_unit_hours, 0) AS total_unit_hours,
    ISNULL(unit_summary.total_progress_percentage, 0) AS total_progress_percentage,
    ISNULL(module_summary.total_modules, 0) AS total_modules,
    ISNULL(module_summary.total_module_hours, 0) AS total_module_hours,
    ISNULL(eval_summary.total_evaluation_percentage, 0) AS total_evaluation_percentage
FROM dbo.didactic_plans AS p
OUTER APPLY (
    SELECT
        COUNT(*) AS total_units,
        SUM(u.planned_hours) AS total_unit_hours,
        SUM(u.progress_percentage) AS total_progress_percentage
    FROM dbo.didactic_plan_units AS u
    WHERE u.didactic_plan_id = p.id
) AS unit_summary
OUTER APPLY (
    SELECT
        COUNT(*) AS total_modules,
        SUM(m.planned_hours) AS total_module_hours
    FROM dbo.didactic_plan_modules AS m
    INNER JOIN dbo.didactic_plan_units AS u
        ON u.id = m.didactic_plan_unit_id
    WHERE u.didactic_plan_id = p.id
) AS module_summary
OUTER APPLY (
    SELECT
        SUM(ec.weight_percentage) AS total_evaluation_percentage
    FROM dbo.didactic_plan_evaluation_criteria AS ec
    WHERE ec.didactic_plan_id = p.id
) AS eval_summary;
GO

CREATE TRIGGER dbo.trg_didactic_plans_block_locked_update
ON dbo.didactic_plans
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM inserted AS i
        INNER JOIN deleted AS d
            ON d.id = i.id
        INNER JOIN dbo.planning_statuses AS s
            ON s.id = d.status_id
        WHERE s.is_teacher_editable = 0
          AND (
                ISNULL(i.teacher_subject_assignment_id, -1) <> ISNULL(d.teacher_subject_assignment_id, -1)
             OR ISNULL(i.general_objective, N'') <> ISNULL(d.general_objective, N'')
             OR ISNULL(i.course_intent, N'') <> ISNULL(d.course_intent, N'')
             OR ISNULL(i.methodology_notes, N'') <> ISNULL(d.methodology_notes, N'')
             OR ISNULL(i.general_observations, N'') <> ISNULL(d.general_observations, N'')
             OR ISNULL(i.submission_notes, N'') <> ISNULL(d.submission_notes, N'')
          )
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 51000, 'Didactic plans cannot be edited while they are locked for review or already authorized.', 1;
    END;
END;
GO

CREATE TRIGGER dbo.trg_didactic_plans_block_locked_delete
ON dbo.didactic_plans
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM deleted AS d
        INNER JOIN dbo.planning_statuses AS s
            ON s.id = d.status_id
        WHERE s.is_teacher_deletable = 0
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 51001, 'Didactic plans cannot be deleted after they have been submitted for review.', 1;
    END;
END;
GO

CREATE TRIGGER dbo.trg_didactic_plan_units_block_locked_dml
ON dbo.didactic_plan_units
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM (
            SELECT didactic_plan_id FROM inserted
            UNION
            SELECT didactic_plan_id FROM deleted
        ) AS x
        INNER JOIN dbo.didactic_plans AS p
            ON p.id = x.didactic_plan_id
        INNER JOIN dbo.planning_statuses AS s
            ON s.id = p.status_id
        WHERE s.is_teacher_editable = 0
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 51002, 'Units cannot be modified while the didactic plan is locked.', 1;
    END;
END;
GO

CREATE TRIGGER dbo.trg_didactic_plan_modules_block_locked_dml
ON dbo.didactic_plan_modules
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM (
            SELECT didactic_plan_unit_id FROM inserted
            UNION
            SELECT didactic_plan_unit_id FROM deleted
        ) AS x
        INNER JOIN dbo.didactic_plan_units AS u
            ON u.id = x.didactic_plan_unit_id
        INNER JOIN dbo.didactic_plans AS p
            ON p.id = u.didactic_plan_id
        INNER JOIN dbo.planning_statuses AS s
            ON s.id = p.status_id
        WHERE s.is_teacher_editable = 0
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 51003, 'Modules cannot be modified while the didactic plan is locked.', 1;
    END;
END;
GO

CREATE TRIGGER dbo.trg_didactic_plan_evaluation_criteria_block_locked_dml
ON dbo.didactic_plan_evaluation_criteria
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM (
            SELECT didactic_plan_id FROM inserted
            UNION
            SELECT didactic_plan_id FROM deleted
        ) AS x
        INNER JOIN dbo.didactic_plans AS p
            ON p.id = x.didactic_plan_id
        INNER JOIN dbo.planning_statuses AS s
            ON s.id = p.status_id
        WHERE s.is_teacher_editable = 0
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 51004, 'Evaluation criteria cannot be modified while the didactic plan is locked.', 1;
    END;
END;
GO

CREATE TRIGGER dbo.trg_didactic_plan_references_block_locked_dml
ON dbo.didactic_plan_references
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (
        SELECT 1
        FROM (
            SELECT didactic_plan_id FROM inserted
            UNION
            SELECT didactic_plan_id FROM deleted
        ) AS x
        INNER JOIN dbo.didactic_plans AS p
            ON p.id = x.didactic_plan_id
        INNER JOIN dbo.planning_statuses AS s
            ON s.id = p.status_id
        WHERE s.is_teacher_editable = 0
    )
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 51005, 'References cannot be modified while the didactic plan is locked.', 1;
    END;
END;
GO
