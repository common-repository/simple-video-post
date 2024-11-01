<?php

/**
 * Single Video Page.
 *
 * @link    http://divyarthinfotech.com
 * @since   1.0.0
 *
 * @package Simple_Video_Post
 */
?>
<style>
.site__row {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-flex-wrap: wrap;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}
.video-toolbar {
    background-color: rgba(0,0,0,0.9);
	width: 100%;
    height: 55px;
}
.dark-background {
    color: #c4c4c4;
}
.video-toolbar .tb-left {
    float: left;
}
.video-toolbar .tb-right {
    float: right;
}
.video-toolbar .tb-left>.site__row, .video-toolbar .tb-right>.site__row {
    margin: 0;
}
.video-toolbar .tb-right .toolbar-item {
    border: none;
}
.video-toolbar .toolbar-item-content {
    height: 55px;
    line-height: 55px;
    padding: 0 30px;
    cursor: pointer;
    position: relative;
}
.video-toolbar .toolbar-item .item-icon, .video-toolbar .toolbar-item .item-text {
    display: inline-block;
    vertical-align: middle;
    line-height: 1;
    transition: color .3s,background-color .3s,border-color .3s;
    -webkit-transition: color .3s,background-color .3s,border-color .3s;
    user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
    -moz-user-select: none;
}
.video-toolbar .toolbar-item .item-icon+.item-text, .video-toolbar .toolbar-item .item-text+.item-icon {
    padding-left: 10px;
}
.light-off {
    opacity: 0;
    visibility: hidden;
    background-color: rgba(0,0,0,.85);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 20;
    transition: opacity .3s,visibility .3s;
    -webkit-transition: opacity .3s,visibility .3s;
}
body.light-off-enabled .light-off {
    opacity: 1;
    visibility: visible;
}
</style>
<div class="svp svp-single-video">
    <!-- Player -->
    <?php if($attributes['show_video'] == 'yes'): echo the_svp_player( esc_attr($attributes['id']) ); ?>
	<div class="light-off light-off-control"></div>
	<div class="video-toolbar dark-background video-toolbar-control">
	<div class="tb-left"><div class="site__row">
	<!--<div class="site__col toolbar-item"> <div class="toolbar-item-content turn-off-light turn-off-light-control"> <span class="item-icon font-size-18"><i class="fa fa-lightbulb-o" aria-hidden="true"></i></span><span class="item-text">Turn Off Light</span> </div> </div>-->
	</div></div>
	<div class="tb-right">
	  <div class="site__row">
		 <div class="site__col toolbar-item">
			<div class="toolbar-item-content comment-video-control scroll-elm-control" data-href="#comments"> <span class="item-text"><?php
                $views_count = get_post_meta( get_the_ID(), 'views', true );
                printf( esc_html__( '%d views', 'simple-video-post' ), $views_count );
                ?></span><span class="item-icon font-size-18"><i class="fa fa-eye" aria-hidden="true"></i></span> </div>
		 </div>
	  </div>
	</div>
	</div>
	<?php  endif; ?>
	
	<br/>
    <!-- Description -->
    <div class="svp-description"><?php echo wp_kses_post($content); ?></div>
  
</div>