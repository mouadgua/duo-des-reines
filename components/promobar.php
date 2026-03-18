<?php 

// Récupération de la barre de promotion
  $queryPromoBar = $pdo->query("SELECT * FROM promotion_bar LIMIT 1");
  $promoBar = $queryPromoBar->fetch();

?>

<?php if ($promoBar && $promoBar['is_active']): ?>
        <div class="bg-[#FDF8F5] border-b border-[#E6C98D]/20 py-2 px-4 text-center">
            <p class="text-[10px] tracking-[0.3em] uppercase font-bold text-[#D4AF37] animate-pulse">
                <?php echo $promoBar['text_' . $lang]; ?>
            </p>
        </div>
<?php endif; ?>