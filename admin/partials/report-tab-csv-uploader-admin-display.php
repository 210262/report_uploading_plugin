<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://
 * @since      1.0.0
 *
 * @package    Report_Tab_Csv_Uploader
 * @subpackage Report_Tab_Csv_Uploader/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.test.com
 * @since      1.0.0
 *
 * @package    Wp_Pro_Quiz_Import_Export
 * @subpackage Wp_Pro_Quiz_Import_Export/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div>
    <h2>ReportTab post Importer</h2>
	<?php

	//get short_title
	function get_short_title( $title ) {
		$pos = stripos( $title, 'market' );
		if ( $pos !== false ) {
			return substr( $title, 0, $pos + 6 );
		}
		$pos = stripos( $title, 'industry' );
		if ( $pos !== false ) {
			return substr( $title, 0, $pos + 8 );
		}

		return $title;
	}

	//trim word
	function rtu_ellipsis( $str, $len = 55 ) {
		$stripped_string = strlen( $str ) > $len ? substr( $str, 0, $len ) . '...' : $str;

		return strip_tags( $stripped_string );
	}

	if ( isset( $_REQUEST['wp-pro-quiz-submit'] ) ) {
		global $wpdb;
		$count = 0;
		$mimes = array( 'application/vnd.ms-excel', 'text/csv', 'text/tsv' );
		if ( ! $_FILES['report-tab-import-post-file'] ) {
			echo( 'No file found' );
		} else if ( ! in_array( $_FILES['report-tab-import-post-file']['type'], $mimes ) ) {
			echo( 'Please upload a csv file' );
		} else {
			$tmp_name = $_FILES['report-tab-import-post-file']['tmp_name'];
			$handle   = fopen( $tmp_name, 'r' );
			$key      = 0;

			$dataArray=["URL"];
			while ( ( $row = fgetcsv( $handle, 1000, "," ) ) !== false ) {
				if ( $key == 0 ) {
					$keys = $row;
					$key ++;
					continue;
				}
				$key ++;
				$row     = array_combine( $keys, $row );
				$postarr = [];
				// New post
				$postarr['post_title']   = $row['title'];
				$postarr['post_content'] = $row['content'];
				//$postarr['post_author'] = $row['author'];
				$postarr['post_status']  = isset( $row['status'] ) ? $row['status'] : 'publish';
				$postarr['post_name']    = isset( $row['short_title'] ) && ( strlen( $row['short_title'] ) ) ? $row['short_title'] : get_short_title( $row['title'] );
				//post meta
				$post_meta                        = [];
				$post_meta['rt_long_description'] = $row['long_description'];
				$post_meta['rt_long_toc']         = $row['long_toc'];
				$post_meta['rt_short_toc']        = $row['short_toc'];
				$post_meta['rt_report_Id']        = $row['report_id'];
				$post_meta['rt_report_URL']       = $row['report_url'];
				$post_meta['rt_site_name']        = $row['site_name'];

				if ( $row['_yoast_wpseo_title'] ) {
					$post_meta['_yoast_wpseo_title'] = $row['_yoast_wpseo_title'];
				}
				if ( $row['_yoast_wpseo_metadesc'] ) {
					$post_meta['_yoast_wpseo_metadesc'] = $row['_yoast_wpseo_metadesc'];
				}
				if ( $row['_yoast_wpseo_focuskw_text_input'] ) {
					$post_meta['_yoast_wpseo_focuskw'] = $row['_yoast_wpseo_focuskw_text_input'];
				}

				//Generate the short TOC automatically based on the Long TOC data
				if ( ( ! $row['short_toc'] || ! strlen( $row['short_toc'] ) ) && $row['long_toc'] ) {
					$post_meta['rt_short_toc'] = rtu_ellipsis( $row['long_toc'], 75 );
				}

				//featured image
				$featured_image_url = $row['featured_image_url'];

				//categories
				if ( isset( $row['categories'] ) ) {
					$categories   = explode( ',', $row['categories'] );
					$category_ids = [];
					foreach ( $categories as $category_name ) {
						$category_ids[] = wp_create_category( $category_name );
					}
					if ( $category_ids && count( $category_ids ) ) {
						$postarr['post_category'] = $category_ids;
					}
				}
				//update
				if ( $postarr['post_title'] ) {
					$existing_post = get_page_by_title( $postarr['post_title'], OBJECT, 'post' );
					if ( $existing_post && $existing_post->ID ) {
						$postarr['ID'] = $existing_post->ID;
					}
				}
				$post_id = wp_insert_post( $postarr, true );
				if ( ! is_wp_error( $post_id ) ) {
					$post = get_post( $post_id );
					if ( $post ) {
						foreach ( $post_meta as $meta_key => $meta_value ) {
							if ( ! add_post_meta( $post_id, $meta_key, $meta_value, true ) ) {
								update_post_meta( $post_id, $meta_key, $meta_value );
							}
						}

						//tags
						if ( isset( $row['tags'] ) ) {
							$tags    = explode( ',', $row['tags'] );
							$tag_ids = [];
							foreach ( $tags as $tag_name ) {
								$tag = wp_create_tag( $tag_name );
								if ( $tag && ! is_wp_error( $tag ) ) {
									$tag_ids[] = + $tag["term_id"];
								}
							}
							if ( $tag_ids && count( $tag_ids ) ) {
								wp_set_post_terms( $post_id, $tag_ids );
							}
						}

						//featured image
						$attachment_id = null;
						$image_ids     = [];
						$random_pos    = 0;
						if ( $featured_image_url ) {
							// 1. featured_image_original_url == $featured_image_url
							// 2. if yes, we will get post_id
							// 2a. from the post_id get the media_id
							// 3. if no, fresh download new media
							// 3a. report.meta['featured_image_origial_url'] = $featured_image_url
							$posts = new WP_Query( "meta_key=featured_image_original_url&meta_value=$featured_image_url" );
							if ( $posts->have_posts() ) {
								if ( ! empty( $posts->posts[0] ) ) {
									$existing_post_info = $posts->posts[0];
									if ( $existing_post_info ) {
										$attachment_id = get_post_thumbnail_id( $existing_post_info->ID );
									}
								}
							}
							if ( ! $attachment_id ) {
								$attachment_id = media_sideload_image( $featured_image_url, $post_id, null, 'id' );
							}
						} else {
							// take any random image from the category's images
							foreach ( $category_ids as $category_id ) {
								$image_ids[] = get_term_meta( $category_id, 'rt_category_image_ids', true );
							}
							$image_ids = explode( ',', implode( ',', $image_ids ) );
							$image_ids = array_filter( $image_ids );
							$image_ids = array_values( $image_ids );
							$image_ids = json_decode( json_encode( $image_ids ), true );
							if ( function_exists( 'random_int' ) ) {
								$random_pos = random_int( 0, sizeof( $image_ids ) - 1 );
							} else {
								mt_srand( time() );
								$random_pos = mt_rand( 0, sizeof( $image_ids ) - 1 );
							}
							$attachment_id = $image_ids[ $random_pos ];
						}
						update_post_meta( $post_id, 'featured_image_pos', $random_pos );
						update_post_meta( $post_id, 'featured_image_candidates', implode( ',', $image_ids ) );
						update_post_meta( $post_id, 'featured_image_attachment_id', $attachment_id );
						update_post_meta( $post_id, 'featured_image_url', $featured_image_url );
//						echo "<pre>";
//						var_dump($attachment_id, $image_ids, $random_pos);
						if ( $attachment_id != null && ! is_wp_error( $attachment_id ) ) {
							$post_meta_id = set_post_thumbnail( $post_id, $attachment_id );
							if ( $featured_image_url && is_string( $featured_image_url ) && strlen( $featured_image_url ) > 0 ) {
								$query = new WP_Query( "meta_key=featured_image_original_url&meta_value=$featured_image_url" );
								$posts = $query->posts;
								foreach ( $posts as $post ) {
									set_post_thumbnail( $post->ID, $attachment_id );
								}
								if ( $post_meta_id && ! add_post_meta( $post_id, 'featured_image_original_url', $featured_image_url, true ) ) {
									update_post_meta( $post_id, 'featured_image_original_url', $featured_image_url );
								}
							}
						}
						$count ++;
						array_push($dataArray, esc_url( get_permalink($post_id) ));

					}
				} else {
					echo "<br> <span style='color: red;'>Error while creating post '" . $postarr['post_title'] . "' </span> <br>";
				}

			}
			if ( $count ) {
				$fileName = 'posts.csv';

//				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//				header('Content-Description: File Transfer');
//				header("Content-type: text/csv");
//				header("Content-Disposition: attachment; filename={$fileName}");
//				header("Expires: 0");
//				header("Pragma: public");
				// Make sure nothing else is sent, our file is done
				$csv = implode('\n', $dataArray);
				echo "<br> <span style='color: green;'>Uploaded</span> <br> <a id='download' href=''>Download</a>";
                ?>
                <script>
                    const url = window.URL.createObjectURL(new Blob(["<?php echo $csv;?>"]));
                    const link = document.getElementById('download');
//                    const link1 = document.createElement('a');
                    link.href = url;
//                    link1.href = url;
                    link.setAttribute('download', 'posts.tsv');
//                    link1.setAttribute('download', 'posts.tsv');
//                    document.body.appendChild(link1);
                    link.click();
                </script>
                <?php

			}
			echo "<br> <b>$count</b> Post processed. <br>";
			echo "<a href='/wp-admin/edit.php' > View posts </a > <br> ";
			echo "<a href='?page=report-tab-csv-uploader' > Back </a > <br> ";
			die();
		}
	}
	?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="report-tab-import-post-file" accept=".csv" required> <br><br>
        <input type="submit" value="Upload" class="button button-primary" name="wp-pro-quiz-submit">
    </form>

    <br><br><br>
    <div class="notice notice - info">
        <p>
            <a href="
	<?php echo plugin_dir_url( __FILE__ ) ?>sample.csv"
               download="sample.csv"> Sample
                csv</a> <br>
            <span>Supported "site_name" are : "orbisresearch"</span>
        </p>
    </div>
</div>


