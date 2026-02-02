# bubble_button.py
from kivy.uix.button import Button
from kivy.uix.bubble import Bubble
from kivy.uix.label import Label
from kivy.core.window import Window
from kivy.clock import Clock
from kivy.metrics import dp
from kivy.properties import StringProperty, BooleanProperty, NumericProperty


class BubbleButton(Button):
    bubble_text = StringProperty("")          # 吹き出しに出す文字
    bubble_width = NumericProperty(dp(240))   # 吹き出しの最大幅
    bubble_padding_x = NumericProperty(dp(10))
    bubble_padding_y = NumericProperty(dp(8))
    close_on_outside = BooleanProperty(True)  # 外側タップで閉じる

    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self._bubble = None
        self.bind(on_release=self._on_press_toggle)

    # --- public ---
    def show_bubble(self):
        if self._bubble:
            return

        b = Bubble(size_hint=(None, None))
        lbl = Label(
            text=self.bubble_text,
            size_hint=(None, None),
            text_size=(self.bubble_width, None),
            padding=(self.bubble_padding_x, self.bubble_padding_y),
            halign="left",
            valign="middle",
        )

        def _update_size(*_):
            lbl.texture_update()
            lbl.width = min(lbl.texture_size[0], self.bubble_width)
            lbl.height = lbl.texture_size[1] + self.bubble_padding_y * 2
            b.width = lbl.width + dp(10)
            b.height = lbl.height + dp(10)
            self._reposition()

        lbl.bind(texture_size=_update_size)
        _update_size()

        b.add_widget(lbl)

        # Window直下に載せると「どの親階層でも確実に最前面」にできる
        Window.add_widget(b)
        self._bubble = b

        # 次フレームで正しい位置に（レイアウト確定後）
        Clock.schedule_once(lambda *_: self._reposition(), 0)

        # 追従
        self.bind(pos=lambda *_: self._reposition(), size=lambda *_: self._reposition())
        Window.bind(on_resize=self._on_resize)

        if self.close_on_outside:
            Window.bind(on_touch_down=self._on_window_touch_down)

    def hide_bubble(self):
        if not self._bubble:
            return
        if self._bubble.parent:
            self._bubble.parent.remove_widget(self._bubble)
        self._bubble = None

        # bind解除（つけっぱなし防止）
        Window.unbind(on_resize=self._on_resize)
        Window.unbind(on_touch_down=self._on_window_touch_down)

    def toggle_bubble(self):
        if self._bubble:
            self.hide_bubble()
        else:
            self.show_bubble()

    # --- internal ---
    def _on_press_toggle(self, *_):
        self.toggle_bubble()

    def _on_resize(self, *_):
        self._reposition()

    def _on_window_touch_down(self, window, touch):
        # 吹き出し表示中のみ
        if not self._bubble:
            return False

        # 自分 or 吹き出し上のタップは無視（トグルや中身操作のため）
        if self.collide_point(*touch.pos):
            return False
        if self._bubble.collide_point(*touch.pos):
            return False

        self.hide_bubble()
        return False

    def _reposition(self):
        if not self._bubble:
            return

        margin = dp(8)

        # ボタンの上中央( Window座標 )
        ax, ay = self.to_window(self.center_x, self.top)

        bx = ax - self._bubble.width / 2
        by = ay + margin

        # 横はみ出し補正
        bx = max(margin, min(bx, Window.width - self._bubble.width - margin))

        # 上にはみ出すなら下に出す
        if by + self._bubble.height > Window.height - margin:
            ax2, ay2 = self.to_window(self.center_x, self.y)
            by = ay2 - self._bubble.height - margin

        by = max(margin, min(by, Window.height - self._bubble.height - margin))

        self._bubble.pos = (bx, by)

    def on_parent(self, *_):
        # 画面遷移などで親から外れたら吹き出しも消す（残骸防止）
        if self.parent is None:
            self.hide_bubble()
