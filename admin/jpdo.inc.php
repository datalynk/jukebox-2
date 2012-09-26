CREATE TABLE `songs` (
	   `id` int(10) not null auto_increment,
	   `album_id` int(10) not null,

	   `track_number` int(3) not null,
	   `title` varchar(255) not null,
	   `artist` varchar(255) not null,
	   `duration` int(10) not null,

	   `file_size` int(10) not null,
	   `file_name` varchar(255) not null,

	   `play_count` int(10) not null default 0,

	   PRIMARY KEY (`id`),
	   KEY `fk_songs_album_id` (`album_id`),
	   CONSTRAINT `fk_files_group_id` FOREIGN KEY (`album_id`) REFERENCES `albums`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `albums` (
	   `id` int(10) not null auto_increment,
	   `artist_id` int(10) not null,

	   `date_added` timestamp default now(),
	   `name` varchar(255) not null,
	   `year` int(4) not null,
	   `path` varchar(255) not null,

	   `size` int(10) not null,
	   `duration` int(10) not null,

	   `num_tracks` int(4) not null,
	   `has_cover` enum('0','1') default '0',

	   PRIMARY KEY (`id`),
	   KEY `fk_albums_artist_id` (`artist_id`),
	   CONSTRAINT `fk_albums_artist_id` FOREIGN KEY (`artist_id`) REFERENCES `artists`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `artists` (
	   `id` int(10) not null auto_increment,
	   `last_updated` timestamp default now(),

	   `artist` varchar(255),
	   
	   PRIMARY KEY (`id`)
) ENGINE=InnoDB;
