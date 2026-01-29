$(".add_btn").click(function () {
    // モーダルウィンドウとオーバーレイをフェードインさせる
    parent.toggleModal(1);
});

$('.modal_open_btn').on('click', function () {
    parent.toggleModal(2);
});

document.querySelector('form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const headingId = document.querySelector('#headingSelect').value;
  const memo = document.querySelector('input[name="memo"]').value.trim();

  // ===== JSバリデーション =====
  if (!headingId) {
    alert('見出しを選択してください');
    return;
  }
  if (memo === '') {
    alert('メモを入力してください');
    return;
  }
  // ===========================


  const fd = new FormData(e.target);
  const res = await fetch('../../store.php', {
    method: 'POST',
    body: fd
  });

  const data = await res.json();
  if (!data.success) {
    alert('保存失敗');
    return;
  }

  // 親をリロード
  parent.location.reload();
});
