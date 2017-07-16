var mediaStream = null;
$(document).ready(function() {
    var video = document.querySelector('video');
    navigator.getUserMedia({
            video: true
        }, function (stream) {
            video.src = window.URL.createObjectURL(stream);
            mediaStream = stream;

        }, function (err) {
            alert(err);
        }
    )
});

var shanpshot = function() {
    var canvas = document.querySelector('canvas');
    var ctx = canvas.getContext('2d');
    var video = document.querySelector('video');
    if (mediaStream) {
        var width = video.offsetWidth
            , height = video.offsetHeight;

        canvas = canvas || document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        ctx.drawImage(video, 0, 0, width, height);
        console.log(canvas.toDataURL('image/png'));
        $('#photo_path').val(canvas.toDataURL('image/png'));
        document.querySelector('img#preview').src = canvas.toDataURL('image/png');
    }
}


$('button#take-photo').on('click', function() {
    shanpshot();
})
