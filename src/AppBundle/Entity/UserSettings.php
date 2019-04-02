<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserSettings
 *
 * @ORM\Table(name="user_settings", indexes={@ORM\Index(name="system_avatar_id", columns={"system_avatar_id"})})
 * @ORM\Entity
 */
class UserSettings
{
    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=16, nullable=true)
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="theme_id", type="string", length=64, nullable=true)
     */
    private $themeId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="final_version_notification", type="boolean", nullable=false)
     */
    private $finalVersionNotification = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="beta_version_notification", type="boolean", nullable=false)
     */
    private $betaVersionNotification = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="main_caption_cutoff", type="integer", nullable=false)
     */
    private $mainCaptionCutoff = '35';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_track_number", type="boolean", nullable=false)
     */
    private $mainTrackNumber = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_artist", type="boolean", nullable=false)
     */
    private $mainArtist = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_album", type="boolean", nullable=false)
     */
    private $mainAlbum = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_genre", type="boolean", nullable=false)
     */
    private $mainGenre = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_year", type="boolean", nullable=false)
     */
    private $mainYear = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_bit_rate", type="boolean", nullable=false)
     */
    private $mainBitRate = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_duration", type="boolean", nullable=false)
     */
    private $mainDuration = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_format", type="boolean", nullable=false)
     */
    private $mainFormat = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="main_file_size", type="boolean", nullable=false)
     */
    private $mainFileSize = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="playlist_caption_cutoff", type="integer", nullable=false)
     */
    private $playlistCaptionCutoff = '35';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_track_number", type="boolean", nullable=false)
     */
    private $playlistTrackNumber = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_artist", type="boolean", nullable=false)
     */
    private $playlistArtist = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_album", type="boolean", nullable=false)
     */
    private $playlistAlbum = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_genre", type="boolean", nullable=false)
     */
    private $playlistGenre = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_year", type="boolean", nullable=false)
     */
    private $playlistYear = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_bit_rate", type="boolean", nullable=false)
     */
    private $playlistBitRate = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_duration", type="boolean", nullable=false)
     */
    private $playlistDuration = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_format", type="boolean", nullable=false)
     */
    private $playlistFormat = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="playlist_file_size", type="boolean", nullable=false)
     */
    private $playlistFileSize = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="last_fm_enabled", type="boolean", nullable=false)
     */
    private $lastFmEnabled = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="last_fm_username", type="string", length=256, nullable=true)
     */
    private $lastFmUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="last_fm_password", type="string", length=256, nullable=true)
     */
    private $lastFmPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="transcode_scheme", type="string", length=32, nullable=false)
     */
    private $transcodeScheme = 'OFF';

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_now_playing", type="boolean", nullable=false)
     */
    private $showNowPlaying = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="selected_music_folder_id", type="integer", nullable=false)
     */
    private $selectedMusicFolderId = '-1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="party_mode_enabled", type="boolean", nullable=false)
     */
    private $partyModeEnabled = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="now_playing_allowed", type="boolean", nullable=false)
     */
    private $nowPlayingAllowed = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="web_player_default", type="boolean", nullable=false)
     */
    private $webPlayerDefault = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_scheme", type="string", length=32, nullable=false)
     */
    private $avatarScheme = 'NONE';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed = '1970-01-01 00:00:00';

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_chat", type="boolean", nullable=false)
     */
    private $showChat = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="song_notification", type="boolean", nullable=false)
     */
    private $songNotification = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_artist_info", type="boolean", nullable=false)
     */
    private $showArtistInfo = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="auto_hide_play_queue", type="boolean", nullable=false)
     */
    private $autoHidePlayQueue = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="view_as_list", type="boolean", nullable=false)
     */
    private $viewAsList = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="default_album_list", type="string", length=64, nullable=false)
     */
    private $defaultAlbumList = 'random';

    /**
     * @var boolean
     *
     * @ORM\Column(name="queue_following_songs", type="boolean", nullable=false)
     */
    private $queueFollowingSongs = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_side_bar", type="boolean", nullable=false)
     */
    private $showSideBar = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_index_in_side_bar", type="boolean", nullable=false)
     */
    private $showIndexInSideBar = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="preferred_video_bitrate", type="integer", nullable=false)
     */
    private $preferredVideoBitrate = '0';

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="username", referencedColumnName="username")
     * })
     */
    private $username;

    /**
     * @var \AppBundle\Entity\SystemAvatar
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SystemAvatar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="system_avatar_id", referencedColumnName="id")
     * })
     */
    private $systemAvatar;


}

