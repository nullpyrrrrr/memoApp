function toggleModal(level) {
    $(`.modal_${level}`).toggleClass('active');
    $(`.overlay_${level}`).toggleClass('active');
}


$('.modal_open_btn').on('click', function () {
    toggleModal(1);
});

$('.overlay').on('click', function () {
    const level = $(this).attr('class').match(/overlay_(\d+)/)[1];
    toggleModal(level);
});

$('.modal_close_btn').on('click', function () {
    const modal = $(this).closest('.modal');
    const level = modal.attr('class').match(/modal_(\d+)/)[1];

    modal.removeClass('active');
    $(`.overlay_${level}`).removeClass('active');
});


window.toggleModal = toggleModal;