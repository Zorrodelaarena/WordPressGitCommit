<?php
/*
Plugin Name: AutoGitCommit
Plugin URI: https://github.com/Zorrodelaarena/auto-git-commit/
Description: Automatically commits and pushes changes made via the wp-admin panel to a git repo
Version: 0.0.1
Author: Ryan Cramer
Author URI: https://secularcoding.com/
License: GPLv2 or later
*/

class AutoGitCommit {
	public static function init() {
		add_action('upgrader_process_complete', __CLASS__ . '::UpgraderProcessComplete', 10, 2);
		add_action('_core_updated_successfully', __CLASS__ . '::CoreUpdatedSuccessfully');
		add_action('activated_plugin', __CLASS__ . '::ActivatedPlugin');
		add_filter('wp_generate_attachment_metadata', __CLASS__ . '::GenerateAttachmentMetadata');
	}

	public static function UpgraderProcessComplete($updater, $options) {
		$message = $options['type'] . ' ' . $options['action'];
		if (isset($options['plugins']) && is_array($options['plugins']) && !empty($options['plugins'])) {
			$message .= ' on ' . implode(' and ', array_map(function($path) {
					return array_shift(explode('/', $path));
				}, $options['plugins']));
		}
		self::CommitWithMessage($message);
	}
	
	public static function ActivatedPlugin($plugin) {
		self::CommitWithMessage('activation of ' . $plugin);
	}
	
	public static function CoreUpdatedSuccessfully($wpVersion) {
		self::CommitWithMessage('upgrade to WordPress version ' . $wpVersion);
	}
	
	public static function GenerateAttachmentMetadata($metadata) {
		self::CommitWithMessage('new media uploaded');
		return $metadata;
	}
	
	private static function CommitWithMessage($message) {
		exec('git add -A && git commit -m ' . escapeshellarg(__CLASS__ . ' triggered by ' . $message) . ' && git push');
	}
}

AutoGitCommit::init();
