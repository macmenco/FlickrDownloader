<?php
require_once ("phpFlickr.php");

$f = new phpFlickr ( "<Your Flickr API key>" );

$share = '<Your location to store photo's>';

$photoset_array = [ 
		[ 
				"photoset_id" => <id 1>,
				"location" => "<subdir on $share>" 
		],
		[ 
				"photoset_id" => <id 2>,
				"location" => "<subdir on $share>" 
		],
];

foreach ( $photoset_array as $photoset_info ) {
	$photoset_id = $photoset_info ['photoset_id'];
	$save_location = $share . $photoset_info ['location'] . "/";
	
	$photoset = $f->photosets_getPhotos ( $photoset_id, null, 1, 500, 1, null );
	
	$photoset_curpage = $photoset ['photoset'] ['page'];
	$photoset_pages = $photoset ['photoset'] ['pages'];
	
	$tel = 0;
	do {
		$photoset = $f->photosets_getPhotos ( $photoset_id, null, 1, 500, $photoset_curpage ++, null );
		// print_r ( $photoset );
		foreach ( $photoset ['photoset'] ['photo'] as $photo ) {
			// echo ++ $tell . "\n";
			// print_r ( $photo );
			$photo_id = $photo ['id'];
			$photo_title = $photo ['title'];
			$photo_sizes = $f->photos_getSizes ( $photo_id );
			// print_r ( $photo_sizes );
			foreach ( $photo_sizes as $photo_size ) {
				// print_r ( $photo_size );
				if ($photo_size ['label'] == 'Original') {
					// print_r ( $photo_size ['source'] );
					$url = $photo_size ['source'];
					// $photo_file = basename ( $url );
					$photo_file_extension = pathinfo ( $url, PATHINFO_EXTENSION );
					set_time_limit ( 0 );
					// This is the file where we save the information
					$sfile = $save_location . $photo_title . "." . $photo_file_extension;
					// print_r ( $sfile );
					$fp = fopen ( $sfile, 'w+' );
					// Here is the file we are downloading, replace spaces with %20
					$ch = curl_init ( str_replace ( " ", "%20", $url ) );
					curl_setopt ( $ch, CURLOPT_TIMEOUT, 50 );
					// write curl response to file
					curl_setopt ( $ch, CURLOPT_FILE, $fp );
					curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
					// get curl response
					curl_exec ( $ch );
					curl_close ( $ch );
					fclose ( $fp );
					echo ++ $tel . " " . $photoset_info ['location'] . "\tDownloaded " . $url . "\n";
					sleep ( 1 );
				}
			}
		}
	} while ( $photoset_curpage <= $photoset_pages );
}

?>
