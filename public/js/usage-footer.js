document.addEventListener('DOMContentLoaded', function () {
    const promptTokensEl = document.getElementById('total-prompt-tokens');
    const completionTokensEl = document.getElementById('total-completion-tokens');
    const totalCostEl = document.getElementById('total-cost');

    const fetchAndRenderStats = async () => {
        if (!promptTokensEl) return; // Don't run if the elements aren't on the page

        try {
            const response = await fetch('/api/usage-stats', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            if (!response.ok) throw new Error('Failed to fetch stats');

            const stats = await response.json();

            promptTokensEl.textContent = stats.total_prompt_tokens.toLocaleString();
            completionTokensEl.textContent = stats.total_completion_tokens.toLocaleString();
            totalCostEl.textContent = `$${stats.total_cost.toFixed(6)}`;

        } catch (error) {
            console.error("Could not fetch usage stats:", error);
            promptTokensEl.textContent = 'N/A';
            completionTokensEl.textContent = 'N/A';
            totalCostEl.textContent = 'N/A';
        }
    };

    // Fetch stats when the page loads
    fetchAndRenderStats();

    // Listen for a custom event that will be dispatched from chat scripts after a message is sent
    document.addEventListener('usageUpdated', () => {
        console.log('Usage updated event received. Refreshing stats...');
        fetchAndRenderStats();
    });
});
