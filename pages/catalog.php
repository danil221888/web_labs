<div class="hero">
  <div class="hero-bg-text">KPI</div>
  <div class="hero-left">
    <div class="hero-eyebrow">// Каталог ігор 2024</div>
    <div class="hero-title">Найкращі<br><em>відеоігри</em></div>
    <p class="hero-sub">Ліцензовані ключі для PC, PlayStation та Xbox. Миттєва доставка на пошту.</p>
  </div>
  <div class="hero-stat">
    <div class="hero-stat-num">{{ filteredGames.length }}</div>
    <div class="hero-stat-label">ігор у каталозі</div>
  </div>
</div>

<div class="filters-wrap">
  <div class="filters">
    <button v-for="f in genres" :key="f.value" class="filter-btn"
            :class="{ active: activeGenre === f.value }" @click="activeGenre = f.value">{{ f.label }}</button>
  </div>
</div>

<div class="catalog">
  <div class="catalog-header">
    <span class="catalog-title">Каталог</span>
    <span class="catalog-count">{{ filteredGames.length }} ігор</span>
    <div style="margin-left:auto">
      <select class="sort-select" v-model="sortBy">
        <option value="default">За замовчуванням</option>
        <option value="price-asc">Ціна ↑</option>
        <option value="price-desc">Ціна ↓</option>
        <option value="rating">Рейтинг ↓</option>
        <option value="discount">Знижка ↓</option>
      </select>
    </div>
  </div>
  <div class="catalog-grid">
    <div v-if="gamesLoading" class="no-results">// завантаження ігор з API...</div>
    <div v-else-if="gamesError" class="no-results" style="color:var(--red)">{{ gamesError }}</div>
    <div v-else-if="filteredGames.length === 0" class="no-results">Ігор не знайдено</div>
    <div v-for="(game, i) in filteredGames" :key="game.id" class="game-card"
         :style="{ animationDelay: i * 0.04 + 's' }" @click="openModal(game)">
      <div class="game-img-wrap">
        <img class="game-img" :src="game.logo" :alt="game.title"
             @error="e => { e.target.style.display='none'; e.target.nextElementSibling.style.display='flex'; }">
        <div class="game-img-placeholder" style="display:none">{{ game.emoji }}</div>
        <span class="card-num">{{ String(i + 1).padStart(2, '0') }}</span>
        <div v-if="game.discount" class="discount-badge">{{ game.discount }}%</div>
        <div class="card-peek"></div>
        <div class="card-peek-label">Детальніше →</div>
        <button class="card-heart" :class="{ wishlisted: wishlist.includes(game.id) }"
                @click.stop="toggleWishlist(game.id)"
                :title="wishlist.includes(game.id) ? 'Прибрати з вішліста' : 'Додати до вішліста'">
          {{ wishlist.includes(game.id) ? '♥' : '♡' }}
        </button>
      </div>
      <div class="game-body">
        <div class="game-genre">{{ genreLabel(game.genre) }}</div>
        <div class="game-title">{{ game.title }}</div>
        <div class="game-dev">{{ game.developer }} · {{ game.year }}</div>
        <div class="game-rating">
          <span class="stars">{{ stars(game.rating) }}</span>
          <span class="rating-num">{{ game.rating.toFixed(1) }}</span>
        </div>
        <div class="game-footer">
          <div class="game-price">
            <span v-if="game.price === 0" class="free-label">Безкоштовно</span>
            <template v-else>
              <span v-if="game.oldPrice" class="old-price">₴{{ fmt(game.oldPrice) }}</span>
              <span class="currency">₴</span>{{ fmt(game.price) }}
            </template>
          </div>
          <button class="add-btn" :class="{ added: isInCart(game.id), pulse: pulsingId === game.id }"
                  :id="'btn-' + game.id" @click.stop="addToCart(game)">
            {{ isInCart(game.id) ? '✓ В кошику' : '+ Купити' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
