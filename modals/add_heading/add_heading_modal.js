document.getElementById('addHeadingForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const fd = new FormData(e.target);

  const res = await fetch('../../api/add_heading.php', {
    method: 'POST',
    body: fd
  });

  const data = await res.json();
  console.log('add_heading response:', data);

  if (data.error) {
    alert('見出しを入力してください');
    return;
  }

  // ===== ここが重要 =====
  const memoIframe = parent.document.querySelector('#memoIframe');
  if (!memoIframe) {
    alert('memoIframe が見つかりません');
    return;
  }

  const memoDoc = memoIframe.contentDocument || memoIframe.contentWindow.document;
  const select = memoDoc.querySelector('#headingSelect');

  if (!select) {
    alert('headingSelect が見つかりません');
    return;
  }
  // ======================

  // 既存 option があるか確認
  let opt = Array.from(select.options)
    .find(o => String(o.value) === String(data.id));

  if (!opt) {
    opt = memoDoc.createElement('option');
    opt.value = data.id;
    opt.textContent = data.heading;
    select.appendChild(opt);
  }

  select.value = data.id;

  // モーダルを閉じる
  if (parent.toggleModal) parent.toggleModal(0);
});
