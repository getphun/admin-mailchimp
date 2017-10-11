INSERT IGNORE INTO `user_perms` ( `name`, `group`, `role`, `about` ) VALUES
    ( 'read_mailchimp',   'Mailchimp', 'Management', 'Allow user to view mailchimp' ),
    ( 'update_mailchimp', 'Mailchimp', 'Management', 'Allow user to update exists mailchimp' ),
    ( 'remove_mailchimp', 'Mailchimp', 'Management', 'Allow user to remove exists mailchimp' ),
    ( 'create_mailchimp', 'Mailchimp', 'Management', 'Allow user to create new mailchimp' );