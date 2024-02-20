<?php
/*
Plugin Name: Give Custom 
Description: Give Custom extention plugin for dashboard
Version: 1.0
Author: Adam
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


//----------------------------------Custom User Dashboard -----------------





function add_custom_meta_box() {
    $screens = array( 'give_forms' ); // Specify the post types where you want to add the meta box

    foreach ( $screens as $screen ) {
        add_meta_box(
            'custom_meta_box', // Unique ID of the meta box
            'Donation URL', // Title of the meta box
            'render_custom_meta_box', // Callback function to render the content of the meta box
            $screen // Post type(s) where the meta box should be added
        );
    }
}
add_action( 'add_meta_boxes', 'add_custom_meta_box' );

// Render meta box content
function render_custom_meta_box( $post ) {
    // Retrieve the current meta value, if it exists
    $custom_meta_value = get_post_meta( $post->ID, 'custom_meta_key', true );

    // Display the input field for the custom meta value
    echo '<label for="custom_meta_field">Custom Meta Field:</label>';
    echo '<input type="text" id="custom_meta_field" name="custom_meta_field" value="' . esc_attr( $custom_meta_value ) . '" />';
}

// Save meta box data
function save_custom_meta_box( $post_id ) {
    if ( isset( $_POST['custom_meta_field'] ) ) {
        $custom_meta_value = sanitize_text_field( $_POST['custom_meta_field'] );
        update_post_meta( $post_id, 'custom_meta_key', $custom_meta_value );
    }
}
add_action( 'save_post', 'save_custom_meta_box' );


function create_shortcode(){





$current_user = wp_get_current_user();

if ( is_user_logged_in() ) :
    $user_id      = get_current_user_id();
    $display_name = $current_user->display_name;


    $donor        = new Give_Donor( $user_id, true );
    $address      = $donor->get_donor_address();
    $company_name = $donor->get_meta( '_give_donor_company', true );
    $donations             = array();
    $donation_history_args = Give()->session->get( 'give_donation_history_args' );
    $donations = give_get_users_donations( get_current_user_id(), 20, true, 'any' );

$currency_code   = give_get_payment_currency_code( $donations[0]->ID );
$donation_amount = give_donation_amount( $donations[0]->ID, true );


$currentDate = date('Y-m-d');

// Calculate the date 6 months ago
$sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));

// Initialize variables for total values
$totalValue = 0;
$totalValueLast6Months = 0;
$i = 0;
// Iterate through the donations
foreach ($donations as $donation) {
    // Get the donation ID and pass it to the give_donation_amount() function
    $donationID = $donation->ID;
    $donationAmount = give_donation_amount($donations[$i]->ID);
    //prinr_r(give_donation_amount($donation->ID, true));

    // Calculate the total value of all donations
    $totalValue += $donationAmount;

    // Check if the donation falls within the last 6 months
    $donationDate = $donation->post_date;
    if ($donationDate >= $sixMonthsAgo && $donationDate <= $currentDate) {
        // Calculate the total value of donations within the last 6 months
        $totalValueLast6Months += $donationAmount;
    }
    $i++;
}


// echo "<pre>";
// print_r($address['country']);
?>

<style>


 /* W3.CSS 4.15 December 2020 by Jan Egil and Borge Refsnes */
html{box-sizing:border-box}*,*:before,*:after{box-sizing:inherit}
/* Extract from normalize.css by Nicolas Gallagher and Jonathan Neal git.io/normalize */
html{-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}
article,aside,details,figcaption,figure,footer,header,main,menu,nav,section{display:block}summary{display:list-item}
audio,canvas,progress,video{display:inline-block}progress{vertical-align:baseline}
audio:not([controls]){display:none;height:0}[hidden],template{display:none}
a{background-color:transparent}a:active,a:hover{outline-width:0}
abbr[title]{border-bottom:none;text-decoration:underline;text-decoration:underline dotted}
b,strong{font-weight:bolder}dfn{font-style:italic}mark{background:#ff0;color:#000}
small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}
sub{bottom:-0.25em}sup{top:-0.5em}figure{margin:1em 40px}img{border-style:none}
code,kbd,pre,samp{font-family:monospace,monospace;font-size:1em}hr{box-sizing:content-box;height:0;overflow:visible}
button,input,select,textarea,optgroup{font:inherit;margin:0}optgroup{font-weight:bold}
button,input{overflow:visible}button,select{text-transform:none}
button,[type=button],[type=reset],[type=submit]{-webkit-appearance:button}
button::-moz-focus-inner,[type=button]::-moz-focus-inner,[type=reset]::-moz-focus-inner,[type=submit]::-moz-focus-inner{border-style:none;padding:0}
button:-moz-focusring,[type=button]:-moz-focusring,[type=reset]:-moz-focusring,[type=submit]:-moz-focusring{outline:1px dotted ButtonText}
fieldset{border:1px solid #c0c0c0;margin:0 2px;padding:.35em .625em .75em}
legend{color:inherit;display:table;max-width:100%;padding:0;white-space:normal}textarea{overflow:auto}
[type=checkbox],[type=radio]{padding:0}
[type=number]::-webkit-inner-spin-button,[type=number]::-webkit-outer-spin-button{height:auto}
[type=search]{-webkit-appearance:textfield;outline-offset:-2px}
[type=search]::-webkit-search-decoration{-webkit-appearance:none}
::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}
/* End extract */
html,body{font-family:Verdana,sans-serif;font-size:15px;line-height:1.5}html{overflow-x:hidden}
h1{font-size:36px}h2{font-size:30px}h3{font-size:24px}h4{font-size:20px}h5{font-size:18px}h6{font-size:16px}
.w3-serif{font-family:serif}.w3-sans-serif{font-family:sans-serif}.w3-cursive{font-family:cursive}.w3-monospace{font-family:monospace}
h1,h2,h3,h4,h5,h6{font-family:"Segoe UI",Arial,sans-serif;font-weight:400;margin:10px 0}.w3-wide{letter-spacing:4px}
hr{border:0;border-top:1px solid #eee;margin:20px 0}
.w3-image{max-width:100%;height:auto}img{vertical-align:middle}a{color:inherit}
.w3-table,.w3-table-all{border-collapse:collapse;border-spacing:0;width:100%;display:table}.w3-table-all{border:1px solid #ccc}
.w3-bordered tr,.w3-table-all tr{border-bottom:1px solid #ddd}.w3-striped tbody tr:nth-child(even){background-color:#f1f1f1}
.w3-table-all tr:nth-child(odd){background-color:#fff}.w3-table-all tr:nth-child(even){background-color:#f1f1f1}
.w3-hoverable tbody tr:hover,.w3-ul.w3-hoverable li:hover{background-color:#ccc}.w3-centered tr th,.w3-centered tr td{text-align:center}
.w3-table td,.w3-table th,.w3-table-all td,.w3-table-all th{padding:8px 8px;display:table-cell;text-align:left;vertical-align:top}
.w3-table th:first-child,.w3-table td:first-child,.w3-table-all th:first-child,.w3-table-all td:first-child{padding-left:16px}
.w3-btn,.w3-button{border:none;display:inline-block;padding:8px 16px;vertical-align:middle;overflow:hidden;text-decoration:none;color:inherit;background-color:inherit;text-align:center;cursor:pointer;white-space:nowrap}
.w3-btn:hover{box-shadow:0 8px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19)}
.w3-btn,.w3-button{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}   
.w3-disabled,.w3-btn:disabled,.w3-button:disabled{cursor:not-allowed;opacity:0.3}.w3-disabled *,:disabled *{pointer-events:none}
.w3-btn.w3-disabled:hover,.w3-btn:disabled:hover{box-shadow:none}
.w3-badge,.w3-tag{background-color:#000;color:#fff;display:inline-block;padding-left:8px;padding-right:8px;text-align:center}.w3-badge{border-radius:50%}
.w3-ul{list-style-type:none;padding:0;margin:0}.w3-ul li{padding:8px 16px;border-bottom:1px solid #ddd}.w3-ul li:last-child{border-bottom:none}
.w3-tooltip,.w3-display-container{position:relative}.w3-tooltip .w3-text{display:none}.w3-tooltip:hover .w3-text{display:inline-block}
.w3-ripple:active{opacity:0.5}.w3-ripple{transition:opacity 0s}
.w3-input{padding:8px;display:block;border:none;border-bottom:1px solid #ccc;width:100%}
.w3-select{padding:9px 0;width:100%;border:none;border-bottom:1px solid #ccc}
.w3-dropdown-click,.w3-dropdown-hover{position:relative;display:inline-block;cursor:pointer}
.w3-dropdown-hover:hover .w3-dropdown-content{display:block}
.w3-dropdown-hover:first-child,.w3-dropdown-click:hover{background-color:#ccc;color:#000}
.w3-dropdown-hover:hover > .w3-button:first-child,.w3-dropdown-click:hover > .w3-button:first-child{background-color:#ccc;color:#000}
.w3-dropdown-content{cursor:auto;color:#000;background-color:#fff;display:none;position:absolute;min-width:160px;margin:0;padding:0;z-index:1}
.w3-check,.w3-radio{width:24px;height:24px;position:relative;top:6px}
.w3-sidebar{height:100%;width:200px;background-color:#fff;position:absolute!important;z-index:1;overflow:auto}
.w3-bar-block .w3-dropdown-hover,.w3-bar-block .w3-dropdown-click{width:100%}
.w3-bar-block .w3-dropdown-hover .w3-dropdown-content,.w3-bar-block .w3-dropdown-click .w3-dropdown-content{min-width:100%}
.w3-bar-block .w3-dropdown-hover .w3-button,.w3-bar-block .w3-dropdown-click .w3-button{width:100%;text-align:left;padding:8px 16px}
.w3-main,#main{transition:margin-left .4s}
.w3-modal{z-index:3;display:none;padding-top:100px;position:fixed;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4)}
.w3-modal-content{margin:auto;background-color:#fff;position:relative;padding:0;outline:0;width:600px}
.w3-bar{width:100%;overflow:hidden}.w3-center .w3-bar{display:inline-block;width:auto}
.w3-bar .w3-bar-item{padding:8px 16px;float:left;width:auto;border:none;display:block;outline:0}
.w3-bar .w3-dropdown-hover,.w3-bar .w3-dropdown-click{position:static;float:left}
.w3-bar .w3-button{white-space:normal}
.w3-bar-block .w3-bar-item{width:100%;display:block;padding:8px 16px;text-align:left;border:none;white-space:normal;float:none;outline:0}
.w3-bar-block.w3-center .w3-bar-item{text-align:center}.w3-block{display:block;width:100%}
.w3-responsive{display:block;overflow-x:auto}
.w3-container:after,.w3-container:before,.w3-panel:after,.w3-panel:before,.w3-row:after,.w3-row:before,.w3-row-padding:after,.w3-row-padding:before,
.w3-cell-row:before,.w3-cell-row:after,.w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after{content:"";display:table;clear:both}
.w3-col,.w3-half,.w3-third,.w3-twothird,.w3-threequarter,.w3-quarter{float:left;width:100%}
.w3-col.s1{width:8.33333%}.w3-col.s2{width:16.66666%}.w3-col.s3{width:24.99999%}.w3-col.s4{width:33.33333%}
.w3-col.s5{width:41.66666%}.w3-col.s6{width:49.99999%}.w3-col.s7{width:58.33333%}.w3-col.s8{width:66.66666%}
.w3-col.s9{width:74.99999%}.w3-col.s10{width:83.33333%}.w3-col.s11{width:91.66666%}.w3-col.s12{width:99.99999%}
@media (min-width:601px){.w3-col.m1{width:8.33333%}.w3-col.m2{width:16.66666%}.w3-col.m3,.w3-quarter{width:24.99999%}.w3-col.m4,.w3-third{width:33.33333%}
.w3-col.m5{width:41.66666%}.w3-col.m6,.w3-half{width:49.99999%}.w3-col.m7{width:58.33333%}.w3-col.m8,.w3-twothird{width:66.66666%}
.w3-col.m9,.w3-threequarter{width:74.99999%}.w3-col.m10{width:83.33333%}.w3-col.m11{width:91.66666%}.w3-col.m12{width:99.99999%}}
@media (min-width:993px){.w3-col.l1{width:8.33333%}.w3-col.l2{width:16.66666%}.w3-col.l3{width:24.99999%}.w3-col.l4{width:33.33333%}
.w3-col.l5{width:41.66666%}.w3-col.l6{width:49.99999%}.w3-col.l7{width:58.33333%}.w3-col.l8{width:66.66666%}
.w3-col.l9{width:74.99999%}.w3-col.l10{width:83.33333%}.w3-col.l11{width:91.66666%}.w3-col.l12{width:99.99999%}}
.w3-rest{overflow:hidden}.w3-stretch{margin-left:-16px;margin-right:-16px}
.w3-content,.w3-auto{margin-left:auto;margin-right:auto}.w3-content{max-width:980px}.w3-auto{max-width:1140px}
.w3-cell-row{display:table;width:100%}.w3-cell{display:table-cell}
.w3-cell-top{vertical-align:top}.w3-cell-middle{vertical-align:middle}.w3-cell-bottom{vertical-align:bottom}
.w3-hide{display:none!important}.w3-show-block,.w3-show{display:block!important}.w3-show-inline-block{display:inline-block!important}
@media (max-width:1205px){.w3-auto{max-width:95%}}
@media (max-width:600px){.w3-modal-content{margin:0 10px;width:auto!important}.w3-modal{padding-top:30px}
.w3-dropdown-hover.w3-mobile .w3-dropdown-content,.w3-dropdown-click.w3-mobile .w3-dropdown-content{position:relative}	
.w3-hide-small{display:none!important}.w3-mobile{display:block;width:100%!important}.w3-bar-item.w3-mobile,.w3-dropdown-hover.w3-mobile,.w3-dropdown-click.w3-mobile{text-align:center}
.w3-dropdown-hover.w3-mobile,.w3-dropdown-hover.w3-mobile .w3-btn,.w3-dropdown-hover.w3-mobile .w3-button,.w3-dropdown-click.w3-mobile,.w3-dropdown-click.w3-mobile .w3-btn,.w3-dropdown-click.w3-mobile .w3-button{width:100%}}
@media (max-width:768px){.w3-modal-content{width:500px}.w3-modal{padding-top:50px}}
@media (min-width:993px){.w3-modal-content{width:900px}.w3-hide-large{display:none!important}.w3-sidebar.w3-collapse{display:block!important}}
@media (max-width:992px) and (min-width:601px){.w3-hide-medium{display:none!important}}
@media (max-width:992px){.w3-sidebar.w3-collapse{display:none}.w3-main{margin-left:0!important;margin-right:0!important}.w3-auto{max-width:100%}}
.w3-top,.w3-bottom{position:fixed;width:100%;z-index:1}.w3-top{top:0}.w3-bottom{bottom:0}
.w3-overlay{position:fixed;display:none;width:100%;height:100%;top:0;left:0;right:0;bottom:0;background-color:rgba(0,0,0,0.5);z-index:2}
.w3-display-topleft{position:absolute;left:0;top:0}.w3-display-topright{position:absolute;right:0;top:0}
.w3-display-bottomleft{position:absolute;left:0;bottom:0}.w3-display-bottomright{position:absolute;right:0;bottom:0}
.w3-display-middle{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%)}
.w3-display-left{position:absolute;top:50%;left:0%;transform:translate(0%,-50%);-ms-transform:translate(-0%,-50%)}
.w3-display-right{position:absolute;top:50%;right:0%;transform:translate(0%,-50%);-ms-transform:translate(0%,-50%)}
.w3-display-topmiddle{position:absolute;left:50%;top:0;transform:translate(-50%,0%);-ms-transform:translate(-50%,0%)}
.w3-display-bottommiddle{position:absolute;left:50%;bottom:0;transform:translate(-50%,0%);-ms-transform:translate(-50%,0%)}
.w3-display-container:hover .w3-display-hover{display:block}.w3-display-container:hover span.w3-display-hover{display:inline-block}.w3-display-hover{display:none}
.w3-display-position{position:absolute}
.w3-circle{border-radius:50%}
.w3-round-small{border-radius:2px}.w3-round,.w3-round-medium{border-radius:4px}.w3-round-large{border-radius:8px}.w3-round-xlarge{border-radius:16px}.w3-round-xxlarge{border-radius:32px}
.w3-row-padding,.w3-row-padding>.w3-half,.w3-row-padding>.w3-third,.w3-row-padding>.w3-twothird,.w3-row-padding>.w3-threequarter,.w3-row-padding>.w3-quarter,.w3-row-padding>.w3-col{padding:0 8px}
.w3-container,.w3-panel{padding:0.01em 16px}.w3-panel{margin-top:16px;margin-bottom:16px}
.w3-code,.w3-codespan{font-family:Consolas,"courier new";font-size:16px}
.w3-code{width:auto;background-color:#fff;padding:8px 12px;border-left:4px solid #4CAF50;word-wrap:break-word}
.w3-codespan{color:crimson;background-color:#f1f1f1;padding-left:4px;padding-right:4px;font-size:110%}
.w3-card,.w3-card-2{box-shadow:0 2px 5px 0 rgba(0,0,0,0.16),0 2px 10px 0 rgba(0,0,0,0.12)}
.w3-card-4,.w3-hover-shadow:hover{box-shadow:0 4px 10px 0 rgba(0,0,0,0.2),0 4px 20px 0 rgba(0,0,0,0.19)}
.w3-spin{animation:w3-spin 2s infinite linear}@keyframes w3-spin{0%{transform:rotate(0deg)}100%{transform:rotate(359deg)}}
.w3-animate-fading{animation:fading 10s infinite}@keyframes fading{0%{opacity:0}50%{opacity:1}100%{opacity:0}}
.w3-animate-opacity{animation:opac 0.8s}@keyframes opac{from{opacity:0} to{opacity:1}}
.w3-animate-top{position:relative;animation:animatetop 0.4s}@keyframes animatetop{from{top:-300px;opacity:0} to{top:0;opacity:1}}
.w3-animate-left{position:relative;animation:animateleft 0.4s}@keyframes animateleft{from{left:-300px;opacity:0} to{left:0;opacity:1}}
.w3-animate-right{position:relative;animation:animateright 0.4s}@keyframes animateright{from{right:-300px;opacity:0} to{right:0;opacity:1}}
.w3-animate-bottom{position:relative;animation:animatebottom 0.4s}@keyframes animatebottom{from{bottom:-300px;opacity:0} to{bottom:0;opacity:1}}
.w3-animate-zoom {animation:animatezoom 0.6s}@keyframes animatezoom{from{transform:scale(0)} to{transform:scale(1)}}
.w3-animate-input{transition:width 0.4s ease-in-out}.w3-animate-input:focus{width:100%!important}
.w3-opacity,.w3-hover-opacity:hover{opacity:0.60}.w3-opacity-off,.w3-hover-opacity-off:hover{opacity:1}
.w3-opacity-max{opacity:0.25}.w3-opacity-min{opacity:0.75}
.w3-greyscale-max,.w3-grayscale-max,.w3-hover-greyscale:hover,.w3-hover-grayscale:hover{filter:grayscale(100%)}
.w3-greyscale,.w3-grayscale{filter:grayscale(75%)}.w3-greyscale-min,.w3-grayscale-min{filter:grayscale(50%)}
.w3-sepia{filter:sepia(75%)}.w3-sepia-max,.w3-hover-sepia:hover{filter:sepia(100%)}.w3-sepia-min{filter:sepia(50%)}
.w3-tiny{font-size:10px!important}.w3-small{font-size:12px!important}.w3-medium{font-size:15px!important}.w3-large{font-size:18px!important}
.w3-xlarge{font-size:24px!important}.w3-xxlarge{font-size:36px!important}.w3-xxxlarge{font-size:48px!important}.w3-jumbo{font-size:64px!important}
.w3-left-align{text-align:left!important}.w3-right-align{text-align:right!important}.w3-justify{text-align:justify!important}.w3-center{text-align:center!important}
.w3-border-0{border:0!important}.w3-border{border:1px solid #ccc!important}
.w3-border-top{border-top:1px solid #ccc!important}.w3-border-bottom{border-bottom:1px solid #ccc!important}
.w3-border-left{border-left:1px solid #ccc!important}.w3-border-right{border-right:1px solid #ccc!important}
.w3-topbar{border-top:6px solid #ccc!important}.w3-bottombar{border-bottom:6px solid #ccc!important}
.w3-leftbar{border-left:6px solid #ccc!important}.w3-rightbar{border-right:6px solid #ccc!important}
.w3-section,.w3-code{margin-top:16px!important;margin-bottom:16px!important}
.w3-margin{margin:16px!important}.w3-margin-top{margin-top:16px!important}.w3-margin-bottom{margin-bottom:16px!important}
.w3-margin-left{margin-left:16px!important}.w3-margin-right{margin-right:16px!important}
.w3-padding-small{padding:4px 8px!important}.w3-padding{padding:8px 16px!important}.w3-padding-large{padding:12px 24px!important}
.w3-padding-16{padding-top:16px!important;padding-bottom:16px!important}.w3-padding-24{padding-top:24px!important;padding-bottom:24px!important}
.w3-padding-32{padding-top:32px!important;padding-bottom:32px!important}.w3-padding-48{padding-top:48px!important;padding-bottom:48px!important}
.w3-padding-64{padding-top:64px!important;padding-bottom:64px!important}
.w3-padding-top-64{padding-top:64px!important}.w3-padding-top-48{padding-top:48px!important}
.w3-padding-top-32{padding-top:32px!important}.w3-padding-top-24{padding-top:24px!important}
.w3-left{float:left!important}.w3-right{float:right!important}
.w3-button:hover{color:#000!important;background-color:#ccc!important}
.w3-transparent,.w3-hover-none:hover{background-color:transparent!important}
.w3-hover-none:hover{box-shadow:none!important}
/* Colors */
.w3-dark_blue,.w3-hover-amber:hover{background: #074b7c; color: #fff;}
.w3-amber,.w3-hover-amber:hover{color:#000!important;background-color:#ffc107!important}
.w3-aqua,.w3-hover-aqua:hover{color:#000!important;background-color:#00ffff!important}
.w3-blue,.w3-hover-blue:hover{color:#fff!important;background-color:#f4a318!important}
.w3-light-blue,.w3-hover-light-blue:hover{color:#000!important;background-color:#87CEEB!important}
.w3-brown,.w3-hover-brown:hover{color:#fff!important;background-color:#795548!important}
.w3-cyan,.w3-hover-cyan:hover{color:#000!important;background-color:#00bcd4!important}
.w3-blue-grey,.w3-hover-blue-grey:hover,.w3-blue-gray,.w3-hover-blue-gray:hover{color:#fff!important;background-color:#607d8b!important}
.w3-green,.w3-hover-green:hover{color:#fff!important;background-color:#4CAF50!important}
.w3-light-green,.w3-hover-light-green:hover{color:#000!important;background-color:#8bc34a!important}
.w3-indigo,.w3-hover-indigo:hover{color:#fff!important;background-color:#3f51b5!important}
.w3-khaki,.w3-hover-khaki:hover{color:#000!important;background-color:#f0e68c!important}
.w3-lime,.w3-hover-lime:hover{color:#000!important;background-color:#cddc39!important}
.w3-orange,.w3-hover-orange:hover{color:#000!important;background-color:#ff9800!important}
.w3-deep-orange,.w3-hover-deep-orange:hover{color:#fff!important;background-color:#ff5722!important}
.w3-pink,.w3-hover-pink:hover{color:#fff!important;background-color:#e91e63!important}
.w3-purple,.w3-hover-purple:hover{color:#fff!important;background-color:#9c27b0!important}
.w3-deep-purple,.w3-hover-deep-purple:hover{color:#fff!important;background-color:#673ab7!important}
.w3-red,.w3-hover-red:hover{color:#fff!important;background-color:#f44336!important}
.w3-sand,.w3-hover-sand:hover{color:#000!important;background-color:#fdf5e6!important}
.w3-teal,.w3-hover-teal:hover{color:#fff!important;background-color:#009688!important}
.w3-yellow,.w3-hover-yellow:hover{color:#000!important;background-color:#ffeb3b!important}
.w3-white,.w3-hover-white:hover{color:#000!important;background-color:#fff!important}
.w3-black,.w3-hover-black:hover{color:#fff!important;background-color:#000!important}
.w3-grey,.w3-hover-grey:hover,.w3-gray,.w3-hover-gray:hover{color:#000!important;background-color:#9e9e9e!important}
.w3-light-grey,.w3-hover-light-grey:hover,.w3-light-gray,.w3-hover-light-gray:hover{color:#000!important;background-color:#f1f1f1!important}
.w3-dark-grey,.w3-hover-dark-grey:hover,.w3-dark-gray,.w3-hover-dark-gray:hover{color:#fff!important;background-color:#616161!important}
.w3-pale-red,.w3-hover-pale-red:hover{color:#000!important;background-color:#ffdddd!important}
.w3-pale-green,.w3-hover-pale-green:hover{color:#000!important;background-color:#ddffdd!important}
.w3-pale-yellow,.w3-hover-pale-yellow:hover{color:#000!important;background-color:#ffffcc!important}
.w3-pale-blue,.w3-hover-pale-blue:hover{color:#000!important;background-color:#ddffff!important}
.w3-text-amber,.w3-hover-text-amber:hover{color:#ffc107!important}
.w3-text-aqua,.w3-hover-text-aqua:hover{color:#00ffff!important}
.w3-text-blue,.w3-hover-text-blue:hover{color:#2196F3!important}
.w3-text-light-blue,.w3-hover-text-light-blue:hover{color:#87CEEB!important}
.w3-text-brown,.w3-hover-text-brown:hover{color:#795548!important}
.w3-text-cyan,.w3-hover-text-cyan:hover{color:#00bcd4!important}
.w3-text-blue-grey,.w3-hover-text-blue-grey:hover,.w3-text-blue-gray,.w3-hover-text-blue-gray:hover{color:#607d8b!important}
.w3-text-green,.w3-hover-text-green:hover{color:#4CAF50!important}
.w3-text-light-green,.w3-hover-text-light-green:hover{color:#8bc34a!important}
.w3-text-indigo,.w3-hover-text-indigo:hover{color:#3f51b5!important}
.w3-text-khaki,.w3-hover-text-khaki:hover{color:#b4aa50!important}
.w3-text-lime,.w3-hover-text-lime:hover{color:#cddc39!important}
.w3-text-orange,.w3-hover-text-orange:hover{color:#ff9800!important}
.w3-text-deep-orange,.w3-hover-text-deep-orange:hover{color:#ff5722!important}
.w3-text-pink,.w3-hover-text-pink:hover{color:#e91e63!important}
.w3-text-purple,.w3-hover-text-purple:hover{color:#9c27b0!important}
.w3-text-deep-purple,.w3-hover-text-deep-purple:hover{color:#673ab7!important}
.w3-text-red,.w3-hover-text-red:hover{color:#f44336!important}
.w3-text-sand,.w3-hover-text-sand:hover{color:#fdf5e6!important}
.w3-text-teal,.w3-hover-text-teal:hover{color:#009688!important}
.w3-text-yellow,.w3-hover-text-yellow:hover{color:#d2be0e!important}
.w3-text-white,.w3-hover-text-white:hover{color:#fff!important}
.w3-text-black,.w3-hover-text-black:hover{color:#000!important}
.w3-text-grey,.w3-hover-text-grey:hover,.w3-text-gray,.w3-hover-text-gray:hover{color:#757575!important}
.w3-text-light-grey,.w3-hover-text-light-grey:hover,.w3-text-light-gray,.w3-hover-text-light-gray:hover{color:#f1f1f1!important}
.w3-text-dark-grey,.w3-hover-text-dark-grey:hover,.w3-text-dark-gray,.w3-hover-text-dark-gray:hover{color:#3a3a3a!important}
.w3-border-amber,.w3-hover-border-amber:hover{border-color:#ffc107!important}
.w3-border-aqua,.w3-hover-border-aqua:hover{border-color:#00ffff!important}
.w3-border-blue,.w3-hover-border-blue:hover{border-color:#2196F3!important}
.w3-border-light-blue,.w3-hover-border-light-blue:hover{border-color:#87CEEB!important}
.w3-border-brown,.w3-hover-border-brown:hover{border-color:#795548!important}
.w3-border-cyan,.w3-hover-border-cyan:hover{border-color:#00bcd4!important}
.w3-border-blue-grey,.w3-hover-border-blue-grey:hover,.w3-border-blue-gray,.w3-hover-border-blue-gray:hover{border-color:#607d8b!important}
.w3-border-green,.w3-hover-border-green:hover{border-color:#4CAF50!important}
.w3-border-light-green,.w3-hover-border-light-green:hover{border-color:#8bc34a!important}
.w3-border-indigo,.w3-hover-border-indigo:hover{border-color:#3f51b5!important}
.w3-border-khaki,.w3-hover-border-khaki:hover{border-color:#f0e68c!important}
.w3-border-lime,.w3-hover-border-lime:hover{border-color:#cddc39!important}
.w3-border-orange,.w3-hover-border-orange:hover{border-color:#ff9800!important}
.w3-border-deep-orange,.w3-hover-border-deep-orange:hover{border-color:#ff5722!important}
.w3-border-pink,.w3-hover-border-pink:hover{border-color:#e91e63!important}
.w3-border-purple,.w3-hover-border-purple:hover{border-color:#9c27b0!important}
.w3-border-deep-purple,.w3-hover-border-deep-purple:hover{border-color:#673ab7!important}
.w3-border-red,.w3-hover-border-red:hover{border-color:#f44336!important}
.w3-border-sand,.w3-hover-border-sand:hover{border-color:#fdf5e6!important}
.w3-border-teal,.w3-hover-border-teal:hover{border-color:#009688!important}
.w3-border-yellow,.w3-hover-border-yellow:hover{border-color:#ffeb3b!important}
.w3-border-white,.w3-hover-border-white:hover{border-color:#fff!important}
.w3-border-black,.w3-hover-border-black:hover{border-color:#000!important}
.w3-border-grey,.w3-hover-border-grey:hover,.w3-border-gray,.w3-hover-border-gray:hover{border-color:#9e9e9e!important}
.w3-border-light-grey,.w3-hover-border-light-grey:hover,.w3-border-light-gray,.w3-hover-border-light-gray:hover{border-color:#f1f1f1!important}
.w3-border-dark-grey,.w3-hover-border-dark-grey:hover,.w3-border-dark-gray,.w3-hover-border-dark-gray:hover{border-color:#616161!important}
.w3-border-pale-red,.w3-hover-border-pale-red:hover{border-color:#ffe7e7!important}.w3-border-pale-green,.w3-hover-border-pale-green:hover{border-color:#e7ffe7!important}
.w3-border-pale-yellow,.w3-hover-border-pale-yellow:hover{border-color:#ffffcc!important}.w3-border-pale-blue,.w3-hover-border-pale-blue:hover{border-color:#e7ffff!important}
/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
}

.w3-main a{
	color:#f4a318!important;
}

a.anc-pdf i {
    font-size: 24px;
}
.tabcontent{
	min-height:100vh !important;
}
#custom_nav_dashboard .w3-button{
	padding:8px 8px !important;
}

div#give-create-account-wrap-1247 {
    display: none !important;
}
</style>
<div class="elementor-section ">
	

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- Top container -->
    
    
    <!-- Sidebar/menu -->
    <nav class="w3-sidebar w3-collapse w3-dark_blue w3-animate-left" style="width:300px;" id="mySidebar"><br>
      <div class="w3-container w3-row">
        <div class="w3-col s4">
			</div>
        <div class="w3-col w3-bar">
          <h3><?php the_title(); ?></h3>
			<span>Welcome <?php echo $display_name; ?></span>
          <!-- <a href="#" class="w3-bar-item w3-button"><i class="fa fa-envelope"></i></a>
          <a href="#" class="w3-bar-item w3-button"><i class="fa fa-user"></i></a>
          <a href="#" class="w3-bar-item w3-button"><i class="fa fa-cog"></i></a> -->
        </div>
      </div>
      <hr>
      <div class="w3-container">
        <!-- <h5>Dashboard</h5> -->
      </div>
      <div class="w3-bar-block">
        <a href="#" class="w3-bar-item w3-button w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black" onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>  Close Menu</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding-16 w3-margin-top w3-blue" onclick="clickHandle(event, 'custom_dashboard', w3_close() )"><i class="fa fa-home fa-fw"></i>Dashboard</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top one-link" onclick="clickHandle(event, 'custom_One_Time_Donations', w3_close())"><i class="fa fa-cc-mastercard fa-fw"></i>  One-Time Donations</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_Subcribtions', w3_close())"><i class="fa fa-circle-o-notch fa-fw"></i>  Subscriptions</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_donate_now', w3_close() )"><i class="fa fa-credit-card-alt fa-fw"></i>  Donate Now</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_Tax_Receipts', w3_close())"><i class="fa fa-file-text-o fa-fw"></i>  Tax Receipts</a>
        <a href="#" class="tablinks w3-bar-item w3-button w3-padding w3-margin-top" onclick="clickHandle(event, 'custom_Settings', w3_close())"><i class="fa fa-cogs fa-fw"></i>  Settings</a>
        <br>
		   <span class="w3-bar-item ">
		<form method="POST" action="">
		  <button type="submit" class="btn_logout" name="submit_logout" style="border:1px solid white; background:#009688; color:white !important; " >Log Out</button>
		</form>
	  </span>
		   <br><br>
      </div>
    </nav>
    
    
    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>
    
    <!-- !PAGE CONTENT! -->
    <div class="w3-main" style="margin-left:300px;">
    
      <!-- Header -->
		<div class="w3-bar w3-black w3-large" style="z-index:0">
      <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey" onclick="w3_open();"><i class="fa fa-bars"></i> Menu</button>
      </div>
<!--       <header class="w3-container w3-show w3-margin-bottom w3-padding-16" id="custom_nav_dashboard" style=" box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"> 
        <a href="#" class="w3-button  w3-white">Home</a>
        <a href="#" class="w3-button w3-white">Who we are</a>
        <a href="#" class="w3-button w3-white">Domestic Projects</a>
        <a href="#" class="w3-button w3-white">International Projects</a>
        <a href="#" class="w3-button w3-white">Contact</a>
      </header>-->
    	
      <div id="custom_dashboard" class="tabcontent w3-row-padding w3-margin-bottom">
        <div class="w3-half">
          <div class="w3-container w3-teal w3-padding-16">
            <div class="w3-left w3-xxxlarge"><i class="fa fa-dollar w3-xxxlarge"></i><?php echo $totalValue; ?></div>
            <div class="w3-right">
              <h3 class="w3-large">This Year</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Total Donations</h4>
          </div>
        </div>
        <div class="w3-half">
            <div class="w3-container w3-blue w3-padding-16">
              <div class="w3-left w3-xxxlarge"><i class="fa fa-dollar w3-xxxlarge"></i><?php echo $totalValueLast6Months; ?></div>
              <div class="w3-right">
                <h3 class="w3-large">6 Months</h3>
              </div>
              <div class="w3-clear"></div>
              <h4>Total Donations</h4>
            </div>
          </div>
        
      
    
      <div class="w3-panel">
        <div class="w3-row-padding" style="margin:0 -16px">
          
          <div class="w3-full">
            <h5 style="margin-top:30px;">Donation by month</h5>
            <table class="w3-table w3-striped w3-white">
              <tr>

                <th>Project Name</th>
                <th>Project Type</th>
                <th>Donation Type</th>
                <th>Amount</th>
              </tr>
        		
				<?php 

// $donation_id     = $donation->ID;
// $donation_number = Give()->seq_donation_number->get_serial_code( $donation_id );
// $form_id         = give_get_payment_meta( $donation_id, '_give_payment_form_id', true );
// $form_name       = give_get_donation_form_title( $donation_id );
// $user            = give_get_payment_meta_user_info( $donation_id );
// $email           = give_get_payment_user_email( $donation_id );
// $status          = $donation->post_status;
// $status_label    = give_get_payment_status( $donation_id, true );
// $company_name    = give_get_payment_meta( $donation_id, '_give_donation_company', true );
// $company_name = $donor->get_meta( '_give_donor_company', true );
//$donations = give_get_users_donations( 0, 20, true, 'any' );
//$donation = give_get_users_donations( $email, give_get_limit_display_donations(), true, 'any' );

	
				$donor        = new Give_Donor( $user_id, true );
				$donations = give_get_users_donations( get_current_user_id(), 20, true, 'any' ); 
				//echo '<pre>';
				//print_r($donation);
				//
				foreach($donations as $dno){
					$donation_data = give_get_payment_meta( $dno->ID );
					$categories = get_the_terms( $donation_data['form_id'], 'give_forms_category' );
					$category_name = $categories[0]->name;
					
					//echo '<pre>';
				   // print_r($donation_data);
					$subs = 0;
					if (isset($donation_data['_give_is_donation_recurring']) && 															$donation_data['_give_is_donation_recurring'] != 0) {
					$subs = 'Monthly Subscription';
				} else {
					$subs = 'One-Time';
				}
					$floatValue = $donation_data['_give_payment_total'];
					$formattedValue = number_format($floatValue, 2, '.', '');
	?>
				
				<tr>
					<td><?php echo $donation_data['_give_payment_form_title']; ?></td>
					<td><?php echo $category_name; ?></td>
					<td><?php echo $subs; ?></td>
<!-- 					<td><?php // echo $address['country']; ?></td> -->
					<td>$ <?php echo $formattedValue; ?></td>
				</tr>
				<?php } ?>

            </table>

<!-- <h5 style="margin-top:30px;">Top 10 Donors</h5> -->
<!--  <table class="w3-table w3-striped w3-white">
              <tr>
				<th>Serial Number</th>
                <th>Donor Name</th>
                <th>Donated Amount</th>
				
              </tr>
			  <?php //$wpdb->prepare("SELECT name, purchase_value FROM wp_give_donors order by purchase_value desc limit 3 WHERE user_id = %s", $user_id);

global $wpdb;

// $query = "SELECT name, purchase_value FROM {$wpdb->prefix}give_donors ORDER BY purchase_value DESC LIMIT 10";

// $results = $wpdb->get_results($query);
// $counter=0;
// if ($results) {
//     foreach ($results as $result) {
// 		 $counter=$counter+1;
//         $name = $result->name;
//         $purchase_value = $result->purchase_value;
        ?>
<tr>
<td><?php // echo $counter; ?></td>
<td><?php // echo $name; ?></td>
<td><?php // echo $purchase_value; ?></td>
</tr>
	 <?php
//     }
// } else {
//     echo "No results found.";
// }
// 			  ?>
			  </table> -->
          </div>
        </div>
      </div>
   </div>
        <!-- One Time Donations-->
  <div id="custom_One_Time_Donations" class="tabcontent one-show">
            <h2>Donations List</h2>
            <?php echo do_shortcode( '[donation_history donor="true" status="true" payment_method="true"]' );?>
            
        </div>
        <!-- Subcribtions-->

        <!-- Subcribtions-->
        <div id="custom_Subcribtions" class="tabcontent">
            <h2>Subscriptions</h2>
			<?php echo do_shortcode('[give_subscriptions]'); ?>
			
			
			
        </div>
        <!-- Subcribtions-->
        
        <!-- Tax Receipts-->
        <div id="custom_Tax_Receipts" class="tabcontent">
            <h2>Annual Receipts</h2>
            <table class="table">
				<thead>
					<tr>
						<td>Annual Reciept Type</td>
						<td>Year</td>
						<td>PDF Download</td>
					</tr>
				</thead>
				<?php $args = array(
                    'post_type'     => 'give_forms',
                    'post_status'   => 'publish',
                    'order'         => 'DESC',
                    
                    'posts_per_page' => -1,
                );
                
                $slider = get_posts($args);
                // echo "<pre>";
                // print_r($slider);
		


                $i = 0;  
                if ($slider) { 
                foreach ($slider as $donation) {
                setup_postdata($donation);
                
                
                $donationID = $donation->ID;
                $donationAmount = give_donation_amount($donations[$i]->ID);
                $email = Give()->session->get( 'give_email' );
                $donor = Give()->donors->get_donor_by( 'email', $email );
                
                $pdate = $donation->post_date; 
                $datetime = new DateTime($pdate);
                $dy = $datetime->format('Y');
//                	echo "<pre>";
//                 print_r($donor->id);
            ?>
              <tr>
                <td><?php echo $donation->post_title; ?></td>
                <td><?php echo $dy; /*echo $donationID;*/ ?></td>
                <td><a class="anc-pdf" href="<?php echo get_home_url();?>/?give_action=preview_annual_receipts&donor=<?php echo $donor->id; ?>&receipt_year=<?php echo $dy; ?>" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a></td>
              </tr>
          <?php $i++; } }else{echo "No Post Found"; } ?>
				
				
			</table>
        </div>
        <!-- Tax Receipts-->
        
        <!-- Settings-->
        <div id="custom_Settings" class="tabcontent">
            <h2>Settings</h2>
            <?php echo do_shortcode( '[give_profile_editor]' );?>
        </div>
        <!-- Settings-->
        <!-- Tabs    -->
        <div id="custom_donate_now" class="tabcontent">
            <div class="w3-row-padding w3-margin-bottom">
                <div class="w3-full">
                <h2>Donate now</h2>
                </div>    
                <?php 

                $args = array(
                    'post_type'     => 'give_forms',
                    'post_status'   => 'publish',
                    'order'         => 'DESC',
                    
                    'posts_per_page' => -1,
                );

                
                
                $slider = get_posts($args);
                // echo "<pre>";
                // print_r($slider);
                //exit;
                $i = 0;   
                foreach ($slider as $donation) { 


                ?>
                <div class="w3-half">
                <div class="w3-container w3-blue w3-padding-16 w3-margin">
                    <!-- <div class="w3-left w3-xxxlarge"><i class="fa fa-dollar w3-xxxlarge"></i>0</div> -->
                    <div class="w3-right">
                    <!-- <h3 class="w3-large">This Years</h3> -->
                    </div>
                    <div class="w3-clear"></div>
                    <h4 class="w3-xlarge"><?php echo $donation->post_title; ?></h4>
                    <h3 class="w3-large"><?php echo $donation->post_excerpt; ?>
                    </h3>
                    <a href="<?php echo get_permalink($donation->ID);  ?>" class="w3-button w3-margin-bottom w3-margin-top w3-white">Donate now</a>
        
                </div>
                </div>
                <?php $i++; } wp_reset_postdata(); ?>


               

            </div>
        </div>
        <!-- Tabs    -->
    
      <!-- End page content -->
    </div>
    
    <script>
    // Get the Sidebar
    var mySidebar = document.getElementById("mySidebar");
    
    // Get the DIV with overlay effect
    var overlayBg = document.getElementById("myOverlay");
    
    // Toggle between showing and hiding the sidebar, and add overlay effect
    function w3_open() {
      if (mySidebar.style.display === 'block') {
        mySidebar.style.display = 'none';
        overlayBg.style.display = "none";
      } else {
        mySidebar.style.display = 'block';
        overlayBg.style.display = "block";
      }
    }
    
    // Close the sidebar with the close button
    function w3_close() {
      mySidebar.style.display = "none";
      overlayBg.style.display = "none";
    }
        
//  Custom Tabs 
    
    function clickHandle(evt, custom_dashboard) {
  let i, tabcontent, tablinks;

  // This is to clear the previous clicked content.
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Set the tab to be "active".
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
    
  }

  var diva = document.querySelector(".give-donation-details a");
  

  // Display the clicked tab and set it to active.
  document.getElementById(custom_dashboard).style.display = "block";
  evt.currentTarget.className += " active";
}
// Simulate a click on the first tab button when the page loads
    window.addEventListener("load", function () {
      document.getElementsByClassName("tablinks")[0].click();
    });



    
    jQuery('.give-donation-details a').click(function(e) {
        localStorage.clear();
        localStorage.setItem("one-link", "active");
        localStorage.setItem("one-show", "show");
    });
    jQuery(document).ready(function() {
        var activeTabWithClass = localStorage.getItem('one-link');
        if (activeTabWithClass) {
            
            setTimeout(function() {
            jQuery('.tablinks').removeClass('active');
            jQuery('.one-link').addClass('active');
            jQuery('.tabcontent').hide();
            jQuery('.one-show').show();
            console.log('Timeout executed!');
            }, 500);
            
        }
    });



    </script>
    
    

</div>


<?php

endif;



// Function to get the donation amount


}

add_shortcode('custom_donation_dashboard', 'create_shortcode');



if (isset($_POST['give_profile_editor_submit'])) {
    $user_id = $_POST['user_id'];
    $address1 = $_POST['_give_donor_address_billing_line1_0'];
    $address2 = $_POST['_give_donor_address_billing_line2_0'];
    $country = $_POST['_give_donor_address_billing_country_0'];
    $state = $_POST['_give_donor_address_billing_state_0'];
    $city = $_POST['_give_donor_address_billing_city_0'];
    $zip = $_POST['_give_donor_address_billing_zip_0'];
    $table_name = $wpdb->prefix . 'give_donormeta';
    global $wpdb;

// Getting Current Donor Id
    $donorID = $wpdb->prepare("SELECT id FROM wp_give_donors WHERE user_id = %s", $user_id);
    $d_ID = $wpdb->get_var($donorID);


if ($d_ID) {
// 	adr 1
        $existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_line1_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_line1_0'", $address1, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_line1_0',
                    'meta_value' => $address1,
                )
            );
        }
//end
	
// 	adr 2
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_line2_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_line2_0'", $address2, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_line2_0',
                    'meta_value' => $address2,
                )
            );
        }
// 	end

// 	adr 3
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_country_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_country_0'", $country, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_country_0',
                    'meta_value' => $country,
                )
            );
        }
// 	end
		
// 	adr 4
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_state_0'));
	//	print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_state_0'", $state, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_state_0',
                    'meta_value' => $state,
                )
            );
        }
// 	end

	// 	adr 5
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_city_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_city_0'", $city, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_city_0',
                    'meta_value' => $city,
                )
            );
        }
// 	end
		
		// 	adr 6
$existing_data = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $table_name WHERE donor_id = %s AND meta_key = %s", $d_ID, '_give_donor_address_billing_zip_0'));
		print_r($existing_data);
        if ($existing_data) {
            $q_update = $wpdb->prepare("UPDATE $table_name SET `meta_value`='%s' WHERE donor_id = %s AND `meta_key`='_give_donor_address_billing_zip_0'", $zip, $d_ID);
            $wpdb->query($q_update);
        }else{
            $wpdb->insert(
                $table_name,
                array(
                    'donor_id' => $d_ID,
                    'meta_key' => '_give_donor_address_billing_zip_0',
                    'meta_value' => $zip,
                )
            );
        }
// 	end
	
    }

    sleep(1);
}