<?php
/**
 * Shared Alert Component
 * Renders Tailwind-styled alerts dynamically based on $message and $message_type.
 */
if (!empty($message)): 
    $is_success = ($message_type === 'success');
?>
<div class="p-4 mb-6 text-sm rounded-xl border flex items-center justify-between shadow-sm animate-fade-in transition-all duration-200 <?php echo $is_success ? 'bg-emerald-50 text-emerald-800 border-emerald-100' : 'bg-rose-50 text-rose-800 border-rose-100'; ?>">
    <div class="flex items-center gap-2.5">
        <?php if ($is_success): ?>
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        <?php else: ?>
            <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        <?php endif; ?>
        <span class="font-medium"><?php echo $message; ?></span>
    </div>
</div>
<?php endif; ?>
