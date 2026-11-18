/**
 * Saayeh theme — front-end behaviour (vanilla JS, no dependencies).
 * jQuery is only used, when present, to listen to WooCommerce's own jQuery events.
 *
 * @license GPL-2.0-or-later
 */
(function () {
	"use strict";

	var S = window.SAAYEH || {};
	var T = S.i18n || {};
	var doc = document;
	var $ = function (sel, ctx) { return (ctx || doc).querySelector(sel); };
	var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); };
	var jq = window.jQuery || null;
	var FD = "۰۱۲۳۴۵۶۷۸۹";

	function fa(s) { return S.faDigits ? String(s).replace(/\d/g, function (d) { return FD[d]; }) : String(s); }
	function en(s) {
		return String(s)
			.replace(/[۰-۹]/g, function (d) { return FD.indexOf(d); })
			.replace(/[٠-٩]/g, function (d) { return "٠١٢٣٤٥٦٧٨٩".indexOf(d); });
	}
	function fmt(tpl, v) { return String(tpl || "").replace("%s", v); }
	function body(obj) {
		var fdata = new FormData();
		Object.keys(obj).forEach(function (k) { if (obj[k] !== undefined && obj[k] !== null) fdata.append(k, obj[k]); });
		return fdata;
	}
	function post(url, data) {
		return fetch(url, { method: "POST", credentials: "same-origin", body: data }).then(function (r) {
			var ct = r.headers.get("content-type") || "";
			return ct.indexOf("json") > -1 ? r.json() : r.text();
		});
	}
	function ajax(action, data) {
		data = data || {};
		data.action = action;
		data.nonce = S.nonce;
		return post(S.ajaxUrl, body(data));
	}
	function wcAjax(endpoint, data) {
		return post(String(S.wcAjaxUrl || "").replace("%%endpoint%%", endpoint), body(data || {}));
	}

	/* ---------------- toast ---------------- */
	var toastTimer;
	function toast(msg, opts) {
		opts = opts || {};
		var el = $("#x-toast");
		if (!el) return;
		$("#x-toast-t").textContent = msg;
		var b = $("#x-toast-b");
		if (b) b.hidden = !opts.cart || !$("#x-drawer");
		el.classList.toggle("is-error", !!opts.error);
		el.classList.add("is-on");
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { el.classList.remove("is-on"); }, opts.error ? 4200 : 2800);
	}

	/* ---------------- fragments ---------------- */
	function applyFragments(fragments, hash, evt) {
		if (!fragments) return;
		Object.keys(fragments).forEach(function (sel) {
			$$(sel).forEach(function (node) {
				var tmp = doc.createElement("div");
				tmp.innerHTML = fragments[sel];
				var fresh = tmp.firstElementChild;
				if (fresh) node.parentNode.replaceChild(fresh, node);
			});
		});
		// Let WooCommerce (cart-fragments sessionStorage) and other plugins know.
		if (jq) {
			try { jq(doc.body).trigger(evt || "added_to_cart", [fragments, hash]); } catch (e) { /* noop */ }
		}
	}

	/* ---------------- drawer ---------------- */
	var drawer = $("#x-drawer"), scrim = $("#x-scrim"), cartBtn = $("#x-cart-btn"), lastFocus = null;
	function openCart() {
		if (!drawer) { if (S.cartUrl) location.href = S.cartUrl; return; }
		lastFocus = doc.activeElement;
		drawer.classList.add("is-open");
		if (scrim) scrim.classList.add("is-open");
		drawer.setAttribute("aria-hidden", "false");
		if (cartBtn) cartBtn.setAttribute("aria-expanded", "true");
		doc.documentElement.style.overflow = "hidden";
		setTimeout(function () { var c = $("#x-close"); if (c) c.focus(); }, 60);
	}
	function closeCart() {
		if (!drawer || !drawer.classList.contains("is-open")) return;
		drawer.classList.remove("is-open");
		if (scrim) scrim.classList.remove("is-open");
		drawer.setAttribute("aria-hidden", "true");
		if (cartBtn) cartBtn.setAttribute("aria-expanded", "false");
		doc.documentElement.style.overflow = "";
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}
	// Focus trap.
	doc.addEventListener("keydown", function (e) {
		if (e.key === "Escape") { closeCart(); closeLive(); }
		if (e.key !== "Tab" || !drawer || !drawer.classList.contains("is-open")) return;
		var f = $$('a[href],button:not([disabled]),input:not([type="hidden"]),select', drawer).filter(function (n) { return n.offsetParent !== null; });
		if (!f.length) return;
		var first = f[0], last = f[f.length - 1];
		if (e.shiftKey && doc.activeElement === first) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && doc.activeElement === last) { e.preventDefault(); first.focus(); }
	});

	/* ---------------- add to cart ---------------- */
	function addToCart(productId, qty, label, btn, silent) {
		if (!S.wcAjaxUrl) return Promise.resolve();
		if (btn) btn.classList.add("is-loading");
		if (drawer) drawer.classList.add("is-loading");
		return wcAjax("add_to_cart", { product_id: productId, quantity: qty || 1 })
			.then(function (res) {
				if (!res || res.error) {
					if (res && res.product_url) { location.href = res.product_url; return; }
					toast(T.error, { error: true });
					return;
				}
				applyFragments(res.fragments, res.cart_hash, "added_to_cart");
				if (!silent) toast(fmt(T.added, label || ""), { cart: true });
				return true;
				if (btn) { btn.classList.add("is-added"); setTimeout(function () { btn.classList.remove("is-added"); }, 1600); }
			})
			.catch(function () { toast(T.error, { error: true }); })
			.then(function () {
				if (btn) btn.classList.remove("is-loading");
				if (drawer) drawer.classList.remove("is-loading");
			});
	}

	function cartQty(key, qty) {
		if (drawer) drawer.classList.add("is-loading");
		return ajax("saayeh_cart_qty", { key: key, qty: qty })
			.then(function (res) {
				if (res && res.fragments) applyFragments(res.fragments, res.cart_hash, "removed_from_cart");
				else if (res && res.data && res.data.message) toast(res.data.message, { error: true });
			})
			.catch(function () { toast(T.error, { error: true }); })
			.then(function () { if (drawer) drawer.classList.remove("is-loading"); });
	}

	function refreshFragments() {
		return wcAjax("get_refreshed_fragments", { time: Date.now() }).then(function (res) {
			if (res && res.fragments) applyFragments(res.fragments, res.cart_hash, "wc_fragments_refreshed");
		});
	}

	/* ---------------- wishlist ---------------- */
	function setWishCount(n) {
		$$(".saayeh-wish-count").forEach(function (c) { c.textContent = fa(n); c.setAttribute("data-count", n); });
	}
	function toggleWish(btn) {
		var id = btn.getAttribute("data-wish");
		var name = btn.getAttribute("data-name") || "";
		$$('[data-wish="' + id + '"]').forEach(function (b) { b.classList.add("is-busy"); });
		ajax("saayeh_wishlist_toggle", { id: id })
			.then(function (res) {
				if (!res || !res.success) { toast(T.error, { error: true }); return; }
				var on = !!res.data.in;
				S.wishlist = res.data.ids;
				$$('[data-wish="' + id + '"]').forEach(function (b) {
					if (b.hasAttribute("data-wish-remove")) return;
					b.classList.toggle("is-on", on);
					b.setAttribute("aria-pressed", on ? "true" : "false");
					b.setAttribute("aria-label", on ? T.wishRemove : T.wishAdd);
				});
				setWishCount(res.data.count);
				toast(fmt(on ? T.wishAdded : T.wishRemoved, res.data.name || name));
				if (!on) {
					var row = $('[data-wish-row="' + id + '"]');
					if (row) {
						row.parentNode.removeChild(row);
						var list = $("[data-saayeh-wishlist]");
						if (list && !$("[data-wish-row]", list)) location.reload();
					}
				}
			})
			.catch(function () { toast(T.error, { error: true }); })
			.then(function () { $$('[data-wish="' + id + '"]').forEach(function (b) { b.classList.remove("is-busy"); }); });
	}

	/* ---------------- quantity stepper ---------------- */
	function stepQty(btn) {
		var wrap = btn.closest(".quantity");
		var input = wrap && $("input.qty", wrap);
		if (!input) return;
		var step = parseFloat(input.step) || 1;
		var min = input.min !== "" ? parseFloat(input.min) : 0;
		var max = input.max !== "" ? parseFloat(input.max) : Infinity;
		var val = parseFloat(en(input.value)) || 0;
		val = Math.min(max, Math.max(min, val + step * parseInt(btn.getAttribute("data-qty"), 10)));
		input.value = val;
		input.dispatchEvent(new Event("input", { bubbles: true }));
		input.dispatchEvent(new Event("change", { bubbles: true }));
	}

	/* ---------------- variation swatches ---------------- */
	function variationsOf(form) {
		if (form._saayehVars !== undefined) return form._saayehVars;
		var raw = form.getAttribute("data-product_variations");
		try { form._saayehVars = raw && raw !== "false" ? JSON.parse(raw) : null; } catch (e) { form._saayehVars = null; }
		return form._saayehVars;
	}
	function currentAttrs(form) {
		var out = {};
		$$(".variations select", form).forEach(function (s) { out[s.getAttribute("data-attribute_name") || s.name] = s.value; });
		return out;
	}
	function syncSwatches(form) {
		var vars = variationsOf(form);
		var cur = currentAttrs(form);
		$$(".x-swatches", form).forEach(function (group) {
			var attr = group.getAttribute("data-attr");
			var select = group.parentNode.querySelector("select");
			var value = select ? select.value : "";
			$$("[data-value]", group).forEach(function (b) {
				var v = b.getAttribute("data-value");
				var on = v === value;
				b.classList.toggle("is-on", on);
				b.setAttribute("aria-pressed", on ? "true" : "false");
				if (!vars) return;
				var anyStock = false, matchStock = false;
				vars.forEach(function (va) {
					var a = va.attributes || {};
					var av = a[attr];
					if (av !== "" && av !== v) return;
					if (!va.is_in_stock || va.is_purchasable === false) return;
					anyStock = true;
					var ok = Object.keys(cur).every(function (k) {
						if (k === attr || !cur[k]) return true;
						return !a[k] || a[k] === cur[k];
					});
					if (ok) matchStock = true;
				});
				var isSize = !b.classList.contains("x-swatch");
				b.classList.toggle("is-off", !matchStock);
				if (isSize) b.disabled = !anyStock;
				var lab = b.textContent || b.getAttribute("aria-label");
				if (!matchStock) b.setAttribute("title", lab + " — " + T.outOfStock); else if (isSize) b.removeAttribute("title");
			});
			// Selected value next to the attribute label.
			var row = group.closest("tr");
			var label = row && $("th label", row);
			if (label && select) {
				var span = $(".x-attr-selected", label);
				if (!span) { span = doc.createElement("span"); span.className = "x-attr-selected"; label.appendChild(span); }
				var opt = select.options[select.selectedIndex];
				span.textContent = select.value && opt ? opt.text : "";
			}
		});
	}
	function initVariationForms() {
		$$("form.variations_form").forEach(function (form) {
			syncSwatches(form);
			if (jq) {
				jq(form).on("woocommerce_variation_has_changed reset_data check_variations found_variation", function () { syncSwatches(form); });
			}
		});
	}

	/* ---------------- live search ---------------- */
	var liveForm = $("[data-live-search]");
	var liveBox = liveForm && $(".x-live", liveForm);
	var liveInput = liveForm && $('input[type="search"]', liveForm);
	var liveTimer, liveSeq = 0, liveIndex = -1;
	function closeLive() {
		if (!liveBox) return;
		liveBox.classList.remove("is-open");
		if (liveInput) liveInput.setAttribute("aria-expanded", "false");
		liveIndex = -1;
	}
	function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
	function runLive() {
		var q = liveInput.value.trim();
		if (q.length < 2) { closeLive(); return; }
		var seq = ++liveSeq;
		var url = S.ajaxUrl + "?action=saayeh_live_search&nonce=" + encodeURIComponent(S.nonce) + "&q=" + encodeURIComponent(q);
		fetch(url, { credentials: "same-origin" }).then(function (r) { return r.json(); }).then(function (res) {
			if (seq !== liveSeq || !res || !res.success) return;
			var items = res.data.items || [];
			var html = items.length ? items.map(function (it, i) {
				return '<a role="option" id="x-live-' + i + '" href="' + esc(it.url) + '">' +
					(it.img ? '<img src="' + esc(it.img) + '" alt="" loading="lazy">' : "<span></span>") +
					"<b>" + esc(it.title) + "</b>" +
					'<span class="price">' + (it.price || "") + "</span></a>";
			}).join("") : '<p class="x-live__empty">' + esc(T.noResults) + "</p>";
			if (items.length && res.data.total > items.length) {
				html += '<a class="x-live__all" href="' + esc(res.data.all) + '">' + esc(T.allResults) + " (" + fa(res.data.total) + ")</a>";
			}
			liveBox.innerHTML = html;
			liveBox.classList.add("is-open");
			liveInput.setAttribute("aria-expanded", "true");
			liveIndex = -1;
		}).catch(function () { /* silent */ });
	}
	if (liveInput && liveBox) {
		liveInput.addEventListener("input", function () { clearTimeout(liveTimer); liveTimer = setTimeout(runLive, 250); });
		liveInput.addEventListener("keydown", function (e) {
			var opts = $$("a", liveBox);
			if (!liveBox.classList.contains("is-open") || !opts.length) return;
			if (e.key === "ArrowDown" || e.key === "ArrowUp") {
				e.preventDefault();
				liveIndex = (liveIndex + (e.key === "ArrowDown" ? 1 : -1) + opts.length) % opts.length;
				opts.forEach(function (o, i) { o.classList.toggle("is-active", i === liveIndex); });
				liveInput.setAttribute("aria-activedescendant", opts[liveIndex].id || "");
			} else if (e.key === "Enter" && liveIndex > -1) {
				e.preventDefault();
				location.href = opts[liveIndex].href;
			}
		});
		doc.addEventListener("click", function (e) { if (!liveForm.contains(e.target)) closeLive(); });
	}

	/* ---------------- checkout stepper + inline validation ---------------- */
	function stepFor(el) {
		var n = el.name || el.id || "";
		if (/^(billing_(first_name|last_name|phone|email)|account_)/.test(n)) return "details";
		if (/^(billing_|shipping_|ship_to_different)/.test(n)) return /shipping_method/.test(n) ? "shipping" : "address";
		if (/shipping_method/.test(n)) return "shipping";
		if (/payment_method|place_order|terms/.test(n) || el.closest && el.closest("#payment")) return "payment";
		if (/order_comments/.test(n)) return "address";
		return null;
	}
	function setStep(key) {
		var st = $("[data-saayeh-stepper]");
		if (!st || !key) return;
		var items = $$("li", st), idx = -1;
		items.forEach(function (li, i) { if (li.getAttribute("data-step") === key) idx = i; });
		if (idx < 0) return;
		items.forEach(function (li, i) {
			li.classList.toggle("is-on", i === idx);
			li.classList.toggle("is-done", i < idx);
			if (i === idx) li.setAttribute("aria-current", "step"); else li.removeAttribute("aria-current");
		});
	}
	function fieldError(input, msg) {
		var row = input.closest(".form-row");
		if (!row) return;
		var p = $(".x-err", row);
		if (msg) {
			row.classList.add("woocommerce-invalid");
			row.classList.remove("woocommerce-validated");
			if (!p) { p = doc.createElement("p"); p.className = "x-err"; p.setAttribute("role", "alert"); row.appendChild(p); }
			p.textContent = msg;
		} else if (p) {
			p.parentNode.removeChild(p);
		}
	}
	function validateIran(input) {
		var v = en(input.value).replace(/[\s-]/g, "");
		if (input.value !== v && /^[\d]*$/.test(v)) input.value = v;
		if (!v) { fieldError(input, ""); return; }
		var country = $("#billing_country");
		if (country && country.value && country.value !== "IR") { fieldError(input, ""); return; }
		if (/_phone$/.test(input.name)) fieldError(input, /^09\d{9}$/.test(v) ? "" : T.badMobile);
		if (/_postcode$/.test(input.name)) fieldError(input, /^\d{10}$/.test(v) ? "" : T.badPostcode);
	}
	var coForm = $("form.woocommerce-checkout");
	if (coForm) {
		coForm.addEventListener("focusin", function (e) { setStep(stepFor(e.target)); });
		coForm.addEventListener("change", function (e) { setStep(stepFor(e.target)); });
		coForm.addEventListener("focusout", function (e) {
			var t = e.target;
			if (t.matches && t.matches("#billing_phone, #billing_postcode, #shipping_postcode")) validateIran(t);
		});
	}
	// Edit-address forms in My Account.
	$$(".woocommerce-address-fields #billing_phone, .woocommerce-address-fields #billing_postcode, .woocommerce-address-fields #shipping_postcode").forEach(function (i) {
		i.addEventListener("blur", function () { validateIran(i); });
	});

	/* ---------------- delegated clicks ---------------- */
	doc.addEventListener("click", function (e) {
		var t = e.target.closest("[data-add],[data-wish],[data-cart-qty],[data-remove-coupon],[data-qty],.x-swatches [data-value],[data-sizeguide],[data-close-dialog],#x-cart-btn,#x-close,#x-scrim,#x-burger,#x-toast-b,.x-filter-toggle");
		if (!t) return;

		if (t.id === "x-cart-btn") { if (drawer) { e.preventDefault(); openCart(); } return; }
		if (t.id === "x-toast-b") { $("#x-toast").classList.remove("is-on"); openCart(); return; }
		if (t.id === "x-close" || t.id === "x-scrim") { closeCart(); return; }
		if (t.id === "x-burger") {
			var nav = $("#x-nav");
			nav.classList.toggle("is-open");
			t.setAttribute("aria-expanded", nav.classList.contains("is-open") ? "true" : "false");
			return;
		}
		if (t.classList.contains("x-filter-toggle")) {
			var f = $("#x-shop-form");
			if (f) { f.hidden = !f.hidden; t.setAttribute("aria-expanded", f.hidden ? "false" : "true"); }
			return;
		}
		if (t.hasAttribute("data-wish")) { e.preventDefault(); toggleWish(t); return; }
		if (t.hasAttribute("data-add")) {
			e.preventDefault();
			var pid = t.getAttribute("data-add");
			if (t.hasAttribute("data-wish-add")) {
				var sel = $("#ws-" + t.getAttribute("data-wish-add"));
				if (sel) pid = sel.value;
			}
			var size = t.getAttribute("data-size");
			addToCart(pid, 1, (t.getAttribute("data-name") || "") + (size ? "، " + size : ""), t);
			return;
		}
		if (t.hasAttribute("data-cart-qty")) {
			var line = t.closest("[data-key]");
			if (line) { e.preventDefault(); cartQty(line.getAttribute("data-key"), parseInt(t.getAttribute("data-cart-qty"), 10)); }
			return;
		}
		if (t.hasAttribute("data-remove-coupon")) {
			e.preventDefault();
			wcAjax("remove_coupon", { security: S.removeCouponNonce, coupon: t.getAttribute("data-remove-coupon") }).then(refreshFragments);
			return;
		}
		if (t.hasAttribute("data-qty")) { e.preventDefault(); stepQty(t); return; }
		if (t.matches(".x-swatches [data-value]")) {
			e.preventDefault();
			var group = t.closest(".x-swatches");
			var select = group.parentNode.querySelector("select");
			if (select) {
				var v = t.getAttribute("data-value");
				// Re-selecting the active value clears it (like WooCommerce's dropdown).
				select.value = select.value === v ? "" : v;
				select.dispatchEvent(new Event("change", { bubbles: true }));
				var form = t.closest("form.variations_form");
				if (form) setTimeout(function () { syncSwatches(form); }, 30);
			}
			return;
		}
		if (t.hasAttribute("data-sizeguide")) {
			var d = $("#x-sizeguide");
			if (d && d.showModal) d.showModal();
			return;
		}
		if (t.hasAttribute("data-close-dialog")) { var dlg = t.closest("dialog"); if (dlg) dlg.close(); return; }
	});
	// Click on dialog backdrop closes it.
	$$("dialog.x-modal").forEach(function (d) {
		d.addEventListener("click", function (e) { if (e.target === d) d.close(); });
	});

	/* ---------------- delegated submits ---------------- */
	doc.addEventListener("submit", function (e) {
		var form = e.target;

		// Coupon in the drawer.
		if (form.hasAttribute("data-coupon-form")) {
			e.preventDefault();
			var input = $('[name="coupon_code"]', form), msg = $("[data-coupon-msg]");
			var code = input.value.trim();
			if (!code) { if (msg) msg.textContent = T.couponEmpty; input.classList.add("is-bad"); return; }
			if (drawer) drawer.classList.add("is-loading");
			wcAjax("apply_coupon", { security: S.applyCouponNonce, coupon_code: code }).then(function (html) {
				var tmp = doc.createElement("div");
				tmp.innerHTML = typeof html === "string" ? html : "";
				var err = $(".woocommerce-error, .is-error", tmp);
				if (err) {
					if (drawer) drawer.classList.remove("is-loading");
					if (msg) msg.textContent = err.textContent.trim();
					input.classList.add("is-bad");
					return;
				}
				return refreshFragments().then(function () { toast(tmp.textContent.trim() || code); });
			}).catch(function () { toast(T.error, { error: true }); }).then(function () { if (drawer) drawer.classList.remove("is-loading"); });
			return;
		}

		// Newsletter.
		if (form.hasAttribute("data-saayeh-newsletter")) {
			e.preventDefault();
			var em = $('[name="email"]', form), out = form.parentNode.querySelector(".x-news__msg");
			var ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(em.value.trim());
			em.classList.toggle("is-bad", !ok);
			if (!ok) { out.className = "x-err x-news__msg"; out.textContent = T.badEmail; return; }
			ajax("saayeh_newsletter", { email: em.value.trim() }).then(function (res) {
				var good = res && res.success;
				out.className = (good ? "x-ok" : "x-err") + " x-news__msg";
				out.textContent = res && res.data && res.data.message ? res.data.message : T.error;
				if (good) em.value = "";
			});
			return;
		}

		// Shop filters: drop empty params, normalise digits.
		if (form.id === "x-shop-form") {
			$$('input[name="min_price"], input[name="max_price"]', form).forEach(function (i) {
				i.value = en(i.value).replace(/\D/g, "");
				if (!i.value) i.disabled = true;
			});
			$$("fieldset", form).forEach(function (fs) {
				if (!$('input[type="checkbox"]:checked', fs)) $$('input[type="hidden"][name^="query_type_"]', fs).forEach(function (h) { h.disabled = true; });
			});
			return;
		}

		// Single product add to cart via AJAX (drawer opens).
		if (form.matches("form.cart") && drawer && S.wcAjaxUrl && !form.closest(".grouped_form") && !form.classList.contains("grouped_form")) {
			var allowed = /^(add-to-cart|product_id|variation_id|quantity|attribute_.+)$/;
			var extra = $$("input, select, textarea", form).some(function (el) { return el.name && !allowed.test(el.name) && el.type !== "submit" && el.type !== "button"; });
			if (extra) return; // Unknown fields (product add-ons): let WooCommerce handle the full POST.
			var btn = e.submitter || $(".single_add_to_cart_button", form);
			if (btn && btn.classList.contains("disabled")) { e.preventDefault(); toast(T.pickOptions, { error: true }); return; }
			var vid = $('[name="variation_id"]', form);
			var pidEl = $('[name="add-to-cart"]', form);
			var pidVal = vid && vid.value && vid.value !== "0" ? vid.value : (pidEl ? pidEl.value : (btn && btn.value));
			if (!pidVal) return;
			if (form.classList.contains("variations_form") && (!vid || !vid.value || vid.value === "0")) { e.preventDefault(); toast(T.pickOptions, { error: true }); return; }
			e.preventDefault();
			var qtyEl = $('[name="quantity"]', form);
			var title = $(".product_title") ? $(".product_title").textContent.trim() : "";
			var picked = $$(".variations select", form).map(function (s) { var o = s.options[s.selectedIndex]; return s.value && o ? o.text : ""; }).filter(Boolean).join("، ");
			addToCart(pidVal, qtyEl ? en(qtyEl.value) : 1, title + (picked ? "، " + picked : ""), btn, true).then(function (ok) {
				if (ok) openCart();
			});
		}
	});

	/* ---------------- autosubmit selects ---------------- */
	doc.addEventListener("change", function (e) {
		var t = e.target;
		if (t.hasAttribute && t.hasAttribute("data-autosubmit")) {
			var f = t.form || (t.getAttribute("form") && doc.getElementById(t.getAttribute("form")));
			if (f) {
				if (f.requestSubmit) f.requestSubmit(); else f.submit();
			}
		}
	});

	/* ---------------- WooCommerce events ---------------- */
	if (jq) {
		// Fragments refreshed by WooCommerce itself.
		jq(doc.body).on("wc_fragments_refreshed wc_fragments_loaded", function () { /* drawer markup replaced automatically */ });
		// Products added through other WooCommerce buttons (e.g. blocks / plugins).
		jq(doc.body).on("added_to_cart", function (ev, frag, hash, $btn) { if ($btn && $btn.length) openCart(); });
	}

	initVariationForms();
	if (jq) jq(function () { initVariationForms(); });
})();
