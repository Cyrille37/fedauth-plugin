<?php

// default providers order

$fa_order_large = array('google','aol','yahoo');
$fa_order_small = array(); // will be filled alphabetically with all except included in $order_large

// if 'url' contains {username} an input box for it will be shown

// general openid
$fa_providers['openid']['name'] = 'OpenID';
$fa_providers['openid']['url'] = null;
$fa_providers['openid']['type'] = 'openid';

// built-in providers in alphabetical order
$fa_providers['aol']['name'] = 'AOL';
$fa_providers['aol']['url'] = 'http://openid.aol.com/{username}';
$fa_providers['aol']['type'] = 'openid';

$fa_providers['blogger']['name'] = 'Blogger';
$fa_providers['blogger']['url'] = 'http://{username}.blogspot.com/';
$fa_providers['blogger']['type'] = 'openid';

$fa_providers['claimid']['name'] = 'ClaimID';
$fa_providers['claimid']['url'] = 'http://claimid.com/{username}';
$fa_providers['claimid']['type'] = 'openid';

$fa_providers['clickpass']['name'] = 'ClickPass';
$fa_providers['clickpass']['url'] = 'http://clickpass.com/public/{username}';
$fa_providers['clickpass']['type'] = 'openid';

$fa_providers['google']['name'] = 'Google';
$fa_providers['google']['url'] = 'https://www.google.com/accounts/o8/id';
$fa_providers['google']['type'] = 'openid';

$fa_providers['google_profile']['name'] = 'Google Profile';
$fa_providers['google_profile']['url'] = 'http://www.google.com/profiles/{username}';
$fa_providers['google_profile']['type'] = 'openid';

$fa_providers['livejournal']['name'] = 'LiveJournal';
$fa_providers['livejournal']['url'] = 'http://{username}.livejournal.com/';
$fa_providers['livejournal']['type'] = 'openid';

$fa_providers['myopenid']['name'] = 'MyOpenID';
$fa_providers['myopenid']['url'] = 'http://{username}.myopenid.com/';
$fa_providers['myopenid']['type'] = 'openid';

$fa_providers['stackexchange']['name'] = 'StackExchange';
$fa_providers['stackexchange']['url'] = 'https://openid.stackexchange.com/';
$fa_providers['stackexchange']['type'] = 'openid';

$fa_providers['technorati']['name'] = 'Technorati';
$fa_providers['technorati']['url'] = 'http://technorati.com/people/technorati/{username}/';
$fa_providers['technorati']['type'] = 'openid';

$fa_providers['verisign']['name'] = 'Verisign';
$fa_providers['verisign']['url'] = 'http://{username}.pip.verisignlabs.com/';
$fa_providers['verisign']['type'] = 'openid';

$fa_providers['wordpress']['name'] = 'Wordpress';
$fa_providers['wordpress']['url'] = 'http://{username}.wordpress.com/';
$fa_providers['wordpress']['type'] = 'openid';

$fa_providers['yahoo']['name'] = 'Yahoo';
$fa_providers['yahoo']['url'] = 'http://me.yahoo.com/';
$fa_providers['yahoo']['type'] = 'openid';
