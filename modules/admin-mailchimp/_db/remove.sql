DELETE FROM `user_perms_chain` WHERE `user_perms` IN (
    SELECT `id` FROM `user_perms` WHERE `group` = 'Mailchimp'
);

DELETE FROM `user_perms` WHERE `group` = 'Mailchimp';

DELETE FROM `site_param` WHERE `name` IN (
    'mailchimp_app_key',
    'mailchimp_list_id'
);