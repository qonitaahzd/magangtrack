<?php
/**
 * MAGANGTRACK - Shared Footer Component
 * Lokasi berkas: magangtrack/includes/footer.php
 */
?>
        <!-- Overlay Mobile Sidebar Backdrop -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-30 hidden md:hidden"></div>

    </div> <!-- Penutup Flex Outer Layout Container -->

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (backdrop) {
                backdrop.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>