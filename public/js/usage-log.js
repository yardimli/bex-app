document.addEventListener('DOMContentLoaded', function () {
    const usageLogButton = document.getElementById('usage-log-button');
    const usageLogModal = document.getElementById('usage_log_modal');
    const usageLogLoader = document.getElementById('usage-log-loader');
    const usageLogContent = document.getElementById('usage-log-content');
    const usageLogTableBody = document.getElementById('usage-log-table-body');
    const usageLogPagination = document.getElementById('usage-log-pagination');

    if (!usageLogButton) return;

    const fetchLogs = async (url = '/api/usage-logs') => {
        usageLogLoader.style.display = 'block';
        usageLogContent.style.display = 'none';

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch usage logs');
            }

            const result = await response.json();
            renderTable(result.data);
            renderPagination(result);

        } catch (error) {
            console.error('Usage Log Error:', error);
            usageLogTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-error">Could not load logs.</td></tr>`;
        } finally {
            usageLogLoader.style.display = 'none';
            usageLogContent.style.display = 'block';
        }
    };

    const renderTable = (logs) => {
        usageLogTableBody.innerHTML = '';
        if (logs.length === 0) {
            usageLogTableBody.innerHTML = `<tr><td colspan="7" class="text-center">No usage logs found.</td></tr>`;
            return;
        }

        logs.forEach(log => {
            const d = new Date(log.created_at);
            const date = d.toLocaleDateString(undefined, { year: '2-digit', month: '2-digit', day: '2-digit' });
            const time = d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
            const formattedTimestamp = `${date}, ${time}`;

            const row = `
                <tr>
                    <td>${formattedTimestamp}</td>
                    <td>${log.user.name}</td>
                    <td>${log.team ? log.team.name : 'Personal'}</td>
                    <td>${log.llm.name}</td>
                    <td class="text-right">${log.prompt_tokens.toLocaleString()}</td>
                    <td class="text-right">${log.completion_tokens.toLocaleString()}</td>
                    <td class="text-right">$${parseFloat(log.total_cost).toFixed(6)}</td>
                </tr>
            `;
            usageLogTableBody.insertAdjacentHTML('beforeend', row);
        });
    };

    const renderPagination = (result) => {
        usageLogPagination.innerHTML = '';
        if (!result.links || result.links.length <= 3) return; // No need for pagination if only prev/next/current

        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'join';

        result.links.forEach(link => {
            const button = document.createElement('button');
            button.className = `join-item btn btn-sm ${link.active ? 'btn-active' : ''} ${!link.url ? 'btn-disabled' : ''}`;
            button.innerHTML = link.label;
            if (link.url) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    fetchLogs(link.url);
                });
            }
            paginationContainer.appendChild(button);
        });
        usageLogPagination.appendChild(paginationContainer);
    };


    usageLogButton.addEventListener('click', () => {
        usageLogModal.showModal();
        fetchLogs();
    });
});
