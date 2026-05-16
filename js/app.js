const { createApp, ref, computed, watch, onMounted, onUnmounted } = Vue;

const STORAGE_KEY  = 'kpi_store_cart_v1';
const WISHLIST_KEY = 'kpi_store_wishlist_v1';

createApp({
  setup() {

    const _initPage = () => {
      const p = new URLSearchParams(location.search).get('page');
      return ['catalog', 'about', 'faq'].includes(p) ? p : 'catalog';
    };
    const currentPage   = ref(_initPage());
    const staticLoading = ref(false);

    const searchQuery  = ref('');
    const activeGenre  = ref('all');
    const sortBy       = ref('default');

    const games        = ref([]);
    const gamesLoading = ref(false);
    const gamesError   = ref('');

    const fetchGames = async () => {
      gamesLoading.value = true;
      gamesError.value   = '';
      try {
        const res  = await fetch('api/games.php?limit=100');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.error?.message ?? 'API error');
        games.value = json.data;
      } catch (err) {
        gamesError.value = 'Помилка завантаження ігор: ' + err.message;
        console.error('[KPI STORE]', err);
      } finally {
        gamesLoading.value = false;
      }
    };

    const cart         = ref([]);
    const wishlist     = ref([]);
    const modalOpen    = ref(false);
    const selectedGame = ref(null);
    const cartOpen     = ref(false);
    const wishlistOpen = ref(false);
    const toastMsg     = ref('');
    const toastVisible = ref(false);
    const pulsingId    = ref(null);
    let   toastTimer   = null;

    const genres = [
      { value:'all',       label:'Всі'       },
      { value:'action',    label:'Action'    },
      { value:'rpg',       label:'RPG'       },
      { value:'strategy',  label:'Стратегія' },
      { value:'adventure', label:'Пригоди'   },
      { value:'sports',    label:'Спорт'     },
    ];

    const fmt        = n  => n.toLocaleString('uk-UA');
    const stars      = r  => { const f = Math.floor(r), h = r % 1 >= .5; return '★'.repeat(f) + (h ? '½' : ''); };
    const genreLabel = g  => ({ action:'Action', rpg:'RPG', strategy:'Стратегія', adventure:'Пригоди', sports:'Спорт' }[g] || g);
    const isInCart   = id => cart.value.some(c => c.id === id);

    const filteredGames = computed(() => {
      let list = games.value.filter(g => {
        const mg = activeGenre.value === 'all' || g.genre === activeGenre.value;
        const ms = g.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                   g.developer.toLowerCase().includes(searchQuery.value.toLowerCase());
        return mg && ms;
      });
      if (sortBy.value === 'price-asc')  list = [...list].sort((a, b) => a.price - b.price);
      if (sortBy.value === 'price-desc') list = [...list].sort((a, b) => b.price - a.price);
      if (sortBy.value === 'rating')     list = [...list].sort((a, b) => b.rating - a.rating);
      if (sortBy.value === 'discount')   list = [...list].sort((a, b) => (Math.abs(b.discount) || 0) - (Math.abs(a.discount) || 0));
      return list;
    });

    const cartTotal     = computed(() => cart.value.reduce((s, c) => s + c.qty, 0));
    const cartSubtotal  = computed(() => cart.value.reduce((s, c) => s + c.price * c.qty, 0));
    const cartSaved     = computed(() => cart.value.reduce((s, c) => s + ((c.oldPrice || c.price) - c.price) * c.qty, 0));
    const wishlistGames = computed(() => wishlist.value.map(id => games.value.find(g => g.id === id)).filter(Boolean));

    const saveCart     = () => { try { localStorage.setItem(STORAGE_KEY,  JSON.stringify(cart.value));     } catch(e){} };
    const saveWishlist = () => { try { localStorage.setItem(WISHLIST_KEY, JSON.stringify(wishlist.value)); } catch(e){} };

    const showToast = msg => {
      toastMsg.value     = msg;
      toastVisible.value = true;
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => { toastVisible.value = false; }, 2500);
    };

    const navigateTo = async (page, push = true) => {
      if (page === currentPage.value) return;
      if (push) history.pushState({ page }, '', `?page=${page}`);

      if (page === 'catalog') {
        currentPage.value = 'catalog';
        return;
      }

      staticLoading.value = true;
      currentPage.value   = page;

      try {
        const res  = await fetch(`?page=${encodeURIComponent(page)}&partial=1`, {
          headers: { 'X-Partial': 'true' }
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const html = await res.text();

        const el = document.getElementById('static-content');
        if (!el) return;
        el.innerHTML = html;

        el.querySelectorAll('script').forEach(oldScript => {
          const s = document.createElement('script');
          s.textContent = oldScript.textContent;
          oldScript.replaceWith(s);
        });
      } catch (err) {
        console.error('[KPI STORE] Помилка завантаження сторінки:', err);
        location.href = `?page=${page}`;
      } finally {
        staticLoading.value = false;
      }
    };

    const _loadInitialStatic = () => {
      if (currentPage.value !== 'catalog') navigateTo(currentPage.value, false);
    };

    const addToCart = game => {
      const ex = cart.value.find(c => c.id === game.id);
      if (ex) ex.qty++;
      else    cart.value.push({ ...game, qty: 1 });
      saveCart();
      pulsingId.value = game.id;
      setTimeout(() => { pulsingId.value = null; }, 450);
      showToast(`${game.title} додано до кошика`);
    };

    const removeFromCart = id => {
      cart.value = cart.value.filter(c => c.id !== id);
      saveCart();
    };

    const changeQty = (id, delta) => {
      const item = cart.value.find(c => c.id === id);
      if (!item) return;
      item.qty += delta;
      if (item.qty <= 0) removeFromCart(id);
      else saveCart();
    };

    const clearCart = () => {
      cart.value = [];
      saveCart();
      showToast('Кошик очищено');
    };

    const checkout = () => {
      showToast(`Замовлення на ₴${fmt(cartSubtotal.value)} оформлено!`);
      cart.value = [];
      saveCart();
      setTimeout(() => { cartOpen.value = false; }, 1400);
    };

    const toggleWishlist = id => {
      const game = games.value.find(g => g.id === id);
      if (!game) return;
      const idx = wishlist.value.indexOf(id);
      if (idx === -1) { wishlist.value.push(id);       showToast(`${game.title} додано до вішліста ♡`); }
      else            { wishlist.value.splice(idx, 1); showToast(`${game.title} прибрано з вішліста`); }
      saveWishlist();
    };

    const clearWishlist = () => {
      wishlist.value = [];
      saveWishlist();
      showToast('Вішліст очищено');
    };

    const moveAllToCart = () => {
      wishlist.value.forEach(id => {
        if (!cart.value.find(c => c.id === id)) {
          const g = games.value.find(x => x.id === id);
          if (g) cart.value.push({ ...g, qty: 1 });
        }
      });
      saveCart();
      showToast('Всі ігри перенесено до кошика!');
      wishlistOpen.value = false;
    };

    const openModal = game => {
      selectedGame.value           = game;
      modalOpen.value              = true;
      document.body.style.overflow = 'hidden';
    };

    watch(modalOpen, val => {
      if (!val) document.body.style.overflow = '';
    });

    const onKeydown = e => {
      if (e.key === 'Escape') {
        modalOpen.value    = false;
        cartOpen.value     = false;
        wishlistOpen.value = false;
      }
    };

    const onPopstate = e => {
      const page = e.state?.page ?? 'catalog';
      navigateTo(page, false);
    };

    onMounted(async () => {
      try { const s = localStorage.getItem(STORAGE_KEY);  if (s) cart.value     = JSON.parse(s); } catch(e){}
      try { const w = localStorage.getItem(WISHLIST_KEY); if (w) wishlist.value = JSON.parse(w); } catch(e){}
      document.addEventListener('keydown', onKeydown);
      window.addEventListener('popstate', onPopstate);
      history.replaceState({ page: currentPage.value }, '', location.href);
      await fetchGames();
      _loadInitialStatic();
    });

    onUnmounted(() => {
      document.removeEventListener('keydown', onKeydown);
      window.removeEventListener('popstate', onPopstate);
    });

    return {
      currentPage, staticLoading,
      searchQuery, activeGenre, sortBy, genres,
      games, gamesLoading, gamesError,
      cart, wishlist,
      modalOpen, selectedGame, cartOpen, wishlistOpen,
      toastMsg, toastVisible, pulsingId,
      filteredGames, cartTotal, cartSubtotal, cartSaved, wishlistGames,
      fmt, stars, genreLabel, isInCart,
      navigateTo,
      addToCart, removeFromCart, changeQty, clearCart, checkout,
      toggleWishlist, clearWishlist, moveAllToCart, openModal,
    };
  }
}).mount('#app');
