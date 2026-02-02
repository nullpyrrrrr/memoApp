# bubble_button.py
from kivy.uix.button import Button
from kivy.uix.bubble import Bubble
from kivy.uix.floatlayout import FloatLayout
from kivy.uix.boxlayout import BoxLayout
from kivy.core.window import Window
from kivy.clock import Clock
from kivy.metrics import dp
from kivy.properties import ObjectProperty, BooleanProperty, NumericProperty, StringProperty


class _SizeToChild(BoxLayout):
    """
    子のサイズに合わせて自分を自動サイズ化するラッパー。
    - 子が minimum_size を持つ Layout ならそれを使う
    - それ以外は child.size を使う
    """
    def __init__(self, **kwargs):
        super().__init__(orientation="vertical", size_hint=(None, None), **kwargs)
        self.bind(children=lambda *_: self._rebind_child())

    def _rebind_child(self):
        if not self.children:
            return
        child = self.children[0]

        # 既存バインドを気にせず「更新関数」を複数bindしてもOKな軽量運用
        child.bind(size=lambda *_: self._refresh())
        if hasattr(child, "minimum_size"):
            child.bind(minimum_size=lambda *_: self._refresh())

        Clock.schedule_once(lambda *_: self._refresh(), 0)

    def _refresh(self):
        if not self.children:
            return
        child = self.children[0]

        if hasattr(child, "minimum_size"):
            w, h = child.minimum_size
            # minimum_sizeが(0,0)の間はchild.sizeで逃げる
            if w <= 0 or h <= 0:
                w, h = child.size
        else:
            w, h = child.size

        # 0対策
        w = max(1, w)
        h = max(1, h)

        self.size = (w, h)


class BubbleButton(Button):
    """
    押すと吹き出しを出すボタン。
    吹き出しの中身は自由Widgetで、root.pyから生成して差し込める。
    """

    # これが “root.pyから指定したい” の本命
    # Callable[[], Widget] を入れる（Noneなら bubble_text をLabelで表示）
    bubble_content_factory = ObjectProperty(None, allownone=True)

    bubble_text = StringProperty("")  # factory未指定時のフォールバック用
    close_on_outside = BooleanProperty(True)

    # 吹き出しの余白・最大幅など（必要なら拡張）
    bubble_margin = NumericProperty(dp(8))
    bubble_outer_padding = NumericProperty(dp(10))

    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self._bubble = None
        self._bubble_body = None  # _SizeToChild
        self.bind(on_release=self._toggle)

    # --- public API ---
    def show_bubble(self, *, content=None):
        """
        content: Widget を直接渡す（優先）
        contentがNoneなら bubble_content_factory() を呼ぶ
        それも無ければ bubble_text を Label で表示（最終手段）
        """
        if self._bubble:
            return

        # 中身を決定
        if content is None and self.bubble_content_factory:
            content = self.bubble_content_factory()

        if content is None:
            # 最低限のフォールバック（自由Widget要求が主なので基本は使わない想定）
            from kivy.uix.label import Label
            content = Label(text=self.bubble_text, size_hint=(None, None))
            content.texture_update()
            content.size = (content.texture_size[0] + dp(12), content.texture_size[1] + dp(12))

        # Bubble本体
        b = Bubble(size_hint=(None, None))

        # 子にサイズを合わせるラッパー
        body = _SizeToChild()
        body.add_widget(content)

        b.add_widget(body)

        # Windowに載せて常に最前面
        Window.add_widget(b)
        self._bubble = b
        self._bubble_body = body

        # 初期サイズ確定＆配置
        Clock.schedule_once(lambda *_: self._sync_size_and_pos(), 0)

        # 追従（ボタンが動く場合）
        self.bind(pos=lambda *_: self._sync_size_and_pos(),
                  size=lambda *_: self._sync_size_and_pos())
        Window.bind(on_resize=self._on_resize)

        if self.close_on_outside:
            Window.bind(on_touch_down=self._on_window_touch_down)

    def hide_bubble(self):
        if not self._bubble:
            return
        if self._bubble.parent:
            self._bubble.parent.remove_widget(self._bubble)

        self._bubble = None
        self._bubble_body = None

        Window.unbind(on_resize=self._on_resize)
        Window.unbind(on_touch_down=self._on_window_touch_down)

    def toggle_bubble(self):
        if self._bubble:
            self.hide_bubble()
        else:
            self.show_bubble()

    # --- internal ---
    def _toggle(self, *_):
        self.toggle_bubble()

    def _on_resize(self, *_):
        self._sync_size_and_pos()

    def _on_window_touch_down(self, window, touch):
        if not self._bubble:
            return False

        # ボタン上 or 吹き出し上は閉じない
        if self.collide_point(*touch.pos):
            return False
        if self._bubble.collide_point(*touch.pos):
            return False

        self.hide_bubble()
        return False

    def _sync_size_and_pos(self):
        if not self._bubble or not self._bubble_body:
            return

        # bodyのサイズを見てBubbleのサイズを決める
        self._bubble_body._refresh()
        bw, bh = self._bubble_body.size

        pad = self.bubble_outer_padding
        self._bubble.size = (bw + pad, bh + pad)

        # 位置：基本はボタンの上中央、はみ出すなら下
        margin = self.bubble_margin
        ax, ay = self.to_window(self.center_x, self.top)

        bx = ax - self._bubble.width / 2
        by = ay + margin

        bx = max(margin, min(bx, Window.width - self._bubble.width - margin))

        if by + self._bubble.height > Window.height - margin:
            ax2, ay2 = self.to_window(self.center_x, self.y)
            by = ay2 - self._bubble.height - margin

        by = max(margin, min(by, Window.height - self._bubble.height - margin))

        self._bubble.pos = (bx, by)

    def on_parent(self, *_):
        # 画面遷移などで親から外れたら吹き出しも消す（残骸防止）
        if self.parent is None:
            self.hide_bubble()
