<?php

/**
 * @file
 * Count most like on picture post to gonorththailand.
 */

?>
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>GoNorth Like Count</title>
    <link rel="stylesheet" href="css/redmond/jquery-ui-1.8.7.custom.css">
    <link rel="stylesheet" href="css/like-count.css">
    <script src="js/jquery-1.4.4.min.js"></script>
    <script src="js/jquery-ui-1.8.7.custom.min.js"></script>
    <script src="js/date.format.minified.js"></script>
  </head>

  <body>
  <div id="fb-root"></div>
  <h1>GoNorth Like Count</h1>
  <div id="like-count">
    <div id="date-control">
      from: <input type="text" id="date-begin">
      to: <input type="text" id="date-end">
      <button id="get-result">Get Result</button>
      <em id="results-count"></em>
    </div>
    <ul id="results">
    </ul>
  </div>
  <script>
  window.today = function() {
    var d = new Date();
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
  }

  window.tomorrow = function() {
    return new Date(today().getTime() + 86400000);
  }

  window.get_result = function () {
    $('#results').html('Please wait, processing ...');

    var range_begin = $('#date-begin').datepicker('getDate').getTime()/1000;
    var range_end = $('#date-end').datepicker('getDate').getTime()/1000;

    var query =
      "SELECT post_id, attachment, likes, created_time, actor_id, message, permalink " +
      "FROM stream " +
      "WHERE source_id = {0} AND " +
             "actor_id != '151383371550838' AND " +
             "created_time > '" + range_begin + "' AND " +
             "created_time <= '" + range_end + "' " +
      "ORDER BY likes.count DESC " +
      "LIMIT 256";

    var posts = FB.Data.query(query, '151383371550838');
    var users = FB.Data.query(
       "SELECT uid, name " +
       "FROM user " +
       "WHERE uid in " +
       "(SELECT actor_id from {0})", posts);


    FB.Data.waitOn([posts, users], function() {
      $('#results').html('');
      var user_list = {};

      FB.Array.forEach(users.value, function(user) {
        user_list[user.uid] = user;
      });

      FB.Array.forEach(posts.value, function(post) {
          //console.log(post, (post.message).substr(0, 20), post.likes.count);
        if (post.attachment != undefined) {
          var actor = user_list[post.actor_id];
          var created = new Date(post.created_time * 1000);
          var picture = "null";
          if (post.attachment.media != undefined) {
            try {
              picture = post.attachment.media[0].src;
            }
            catch (err) {
                if (typeof console != undefined )
                  console.log(err);
            }
          }
          var item = generate_item(picture, post, actor, created);
          $('#results').append(item);
          $('#results-count').html($('#results li').size() + " results"); 
      }
    }); 
      FB.Canvas.setSize();
    });
  }
  window.generate_item = function(picture, post, actor, created) {
    var item;
    try {
      item = "\
      <li>\
        <div class='item-like'>\
          <span class='item-like-count'>" + post.likes.count + "</span>\
          <img src='" + picture + "'>\
              </div>\
              <div class='item-info'>\
          <div class='item-author'>\
            by: \
            <a href='http://facebook.com/profile.php?id=" + actor.uid + "'>" + actor.name + "</a>\
            when: <em>" + created.format('mmmm dd, yyyy HH:MM') + "</em>\
          </div>\
          <div class='item-message'>" + post.message + "</div>\
          <div class='item-permalink'>link: <a href='" + post.permalink + "'>" + post.permalink + "</a></div>\
        </div>\
        <div style='clear:both'></div>\
      </li>";
    }
    catch (err) {
      if (typeof console != undefined)
        console.log(err);
    } 
    return item; 
  }

  window.fbAsyncInit = function() {
    // Init facebook sdk.
    FB.init({
      appId  : '213496902000002',
      status : false, // check login status
      cookie : true, // enable cookies to allow the server to access the session
      xfbml  : false  // parse XFBML
    });

    get_result();
  };

  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/en_US/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());

  jQuery(document).ready(function ($) {
    $('#date-begin, #date-end').datepicker({
      showButtonPanel : false,
      dateFormat: "dd/mm/yy"
    });

    $('#date-begin').datepicker('setDate', today());
    $('#date-end').datepicker('setDate', tomorrow());

    $('#get-result').click(function (e) {
      get_result();
    });
  });
  </script>
  </body>
</html>
