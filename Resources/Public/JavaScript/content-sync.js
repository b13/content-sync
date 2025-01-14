import $ from 'jquery';

const ContentSync = {};

ContentSync.ajaxCall = function(url) {
  $('#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-toolbar-icon').hide();
  $('#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-toolbar-spinner').show();

  $.ajax({
    type: 'POST',
    url: url,
    success: function(response) {
      if (response.content) {
        $('.t3js-content-sync-current-job').html(response.content);
      }
      top.TYPO3.Notification.showMessage(
        response.flashMessage.title,
        response.flashMessage.message,
        response.flashMessage.severity,
        5
      );
      $('#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-toolbar-spinner').hide();
      $('#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-toolbar-icon').show();
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
      top.TYPO3.Notification.showMessage(
        'Status: ' + textStatus,
        'Error: ' + errorThrown,
        top.TYPO3.Severity.error,
        5
      );
      $('#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-toolbar-spinner').hide();
      $('#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-toolbar-icon').show();
    }
  });
};

ContentSync.init = function() {
  $(document).on('submit', '#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-kill', function(ev) {
    ev.preventDefault();
    ContentSync.ajaxCall(TYPO3.settings.ajaxUrls['content-sync_kill']);
  });

  $(document).on('submit', '#b13-contentsync-backend-toolbaritems-jobstatustoolbaritem .t3js-content-sync-create', function(ev) {
    ev.preventDefault();
    ContentSync.ajaxCall(TYPO3.settings.ajaxUrls['content-sync_create']);
  });
};

$(document).ready(function() {
  ContentSync.init();
});

export default ContentSync;
