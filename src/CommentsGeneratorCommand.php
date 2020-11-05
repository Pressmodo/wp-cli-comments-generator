<?php
/**
 * Generator command.
 *
 * @package   wp-cli-comments-generator
 * @author    Alessandro Tesoro <hello@pressmodo.com>
 * @copyright 2020 Alessandro Tesoro
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://pressmodo.com
 */

namespace Pressmodo\CLI;

use joshtronic\LoremIpsum;

/**
 * Comments generator command.
 */
class CommentsGeneratorCommand {

	/**
	 * Generate random comments for all posts on the website using pre-existing users.
	 *
	 * ## EXAMPLE
	 *
	 *  $ wp comments-generator generate
	 *  Generate comments for all blog posts.
	 *
	 * @return void
	 */
	public function generate() {

		$users = ( new \WP_User_Query( [ 'number' => -1 ] ) )->get_results();

		$posts = ( new \WP_Query(
			[
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		) )->get_posts();

		\WP_CLI::line( '' );
		\WP_CLI::line( sprintf( 'Found %s blog posts.', count( $posts ) ) );
		\WP_CLI::line( sprintf( 'Found %s users.', count( $users ) ) );
		\WP_CLI::line( '' );

		$lipsum = new LoremIpsum();

		$notify = \WP_CLI\Utils\make_progress_bar( 'Generating comment(s) for each blog post found.', count( $posts ) );

		foreach ( $posts as $post_id ) {

			foreach ( $users as $user ) {

				$user_id = $user->ID;
				$agent   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
				$author  = $this->get_user_firstname( $user_id );

				if ( ! $author ) {
					$author = $user->data->display_name;
				}

				$data = [
					'user_id'              => $user_id,
					'comment_post_ID'      => $post_id,
					'comment_author'       => $this->get_user_firstname( $user_id ),
					'comment_author_email' => $user->data->user_email,
					'comment_content'      => $lipsum->sentence(),
					'comment_agent'        => $agent,
					'comment_type'         => '',
					'comment_date'         => gmdate( 'Y-m-d H:i:s' ),
					'comment_date_gmt'     => gmdate( 'Y-m-d H:i:s' ),
					'comment_approved'     => 1,
				];

				wp_insert_comment( $data );

			}

			$notify->tick();

		}

		$notify->finish();

		\WP_CLI::line( '' );
		\WP_CLI::success( 'Done.' );
		\WP_CLI::line( '' );

	}

	/**
	 * Get the user's first name if it exists.
	 *
	 * @param string $user_id
	 * @return string|bool
	 */
	private function get_user_firstname( $user_id ) {
		$data = get_userdata( $user_id );
		return isset( $data->first_name ) && ! empty( $data->first_name ) ? $data->first_name : false;
	}

	/**
	 * Wipe all comments of all blog posts.
	 *
	 * @return void
	 */
	public function wipe() {

		$posts = ( new \WP_Query(
			[
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		) )->get_posts();

		$comments = ( new \WP_Comment_Query() )->query(
			[
				'post__in' => $posts,
				'number'   => '',
				'fields'   => 'ids',
				'type'     => 'comment',
			]
		);

		\WP_CLI::line( '' );
		\WP_CLI::line( sprintf( 'Found %s blog posts.', count( $posts ) ) );
		\WP_CLI::line( sprintf( 'Found %s comments.', count( $comments ) ) );
		\WP_CLI::line( '' );

		if ( count( $comments ) <= 0 ) {
			\WP_CLI::error( 'No comments have been found.' );
		}

		$notify = \WP_CLI\Utils\make_progress_bar( 'Deleting comment(s) for each blog post found.', count( $comments ) );

		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment, true );
			$notify->tick();
		}

		$notify->finish();

		\WP_CLI::line( '' );
		\WP_CLI::success( 'Done.' );
		\WP_CLI::line( '' );

	}

}
