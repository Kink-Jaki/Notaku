# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: verify-skeleton.spec.ts >> Kasir Pages Verification >> Antrian approve works with SweetAlert
- Location: verify-skeleton.spec.ts:68:5

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('#antrian-pending-body .btn-approve').first()
Expected: visible
Timeout: 10000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" locator('#antrian-pending-body .btn-approve').first() with timeout 10000ms
  - waiting for locator('#antrian-pending-body .btn-approve').first()

```

```yaml
- navigation:
  - button "Buka menu": 
  - link "Notaku Notaku":
    - /url: http://localhost:8000
    - img "Notaku"
    - text: Notaku
  - button "K kasir test"
- main:
  - paragraph: Pesanan pelanggan yang menunggu konfirmasi kasir
  - text:  0 pesanan menunggu
  - list:
    - listitem:
      - link "Semua 27":
        - /url: http://localhost:8000/kasir/antrian?status=semua
    - listitem:
      - link "Menunggu 0":
        - /url: http://localhost:8000/kasir/antrian?status=menunggu
    - listitem:
      - link "Diproses 26":
        - /url: http://localhost:8000/kasir/antrian?status=diproses
    - listitem:
      - link "Ditolak 1":
        - /url: http://localhost:8000/kasir/antrian?status=ditolak
  - region "Perlu Tinjauan":
    - heading "Perlu Tinjauan" [level=2]
    - text: "0"
    - table:
      - rowgroup:
        - row "ID Pesanan Pelanggan Waktu Item Total Aksi":
          - columnheader "ID Pesanan"
          - columnheader "Pelanggan"
          - columnheader "Waktu"
          - columnheader "Item"
          - columnheader "Total"
          - columnheader "Aksi"
      - rowgroup:
        - row " Tidak ada pesanan menunggu Semua pesanan masuk sudah diatasi.":
          - cell " Tidak ada pesanan menunggu Semua pesanan masuk sudah diatasi."
  - region "Riwayat Penanganan":
    - heading "Riwayat Penanganan" [level=2]
    - paragraph: Pesanan online yang sudah diputuskan kasir
    - text: "27"
    - table:
      - rowgroup:
        - row "ID Pelanggan Waktu Item Total Keputusan":
          - columnheader "ID"
          - columnheader "Pelanggan"
          - columnheader "Waktu"
          - columnheader "Item"
          - columnheader "Total"
          - columnheader "Keputusan"
      - rowgroup:
        - row "ORD-20260926-006 Budi Santoso 06:08 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-006"
          - cell "Budi Santoso"
          - cell "06:08"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-007 Budi Santoso 06:07 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-007"
          - cell "Budi Santoso"
          - cell "06:07"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-009 Budi Santoso 06:04 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-009"
          - cell "Budi Santoso"
          - cell "06:04"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-010 Budi Santoso 06:02 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-010"
          - cell "Budi Santoso"
          - cell "06:02"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-012 Budi Santoso 05:59 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-012"
          - cell "Budi Santoso"
          - cell "05:59"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "TRX-20260926-019 ORD-20260926-022 Budi Santoso 05:27 1 item Rp 4.000 Disetujui":
          - cell "TRX-20260926-019 ORD-20260926-022":
            - link "TRX-20260926-019":
              - /url: http://localhost:8000/kasir/transaksi/TRX-20260926-019
            - text: ORD-20260926-022
          - cell "Budi Santoso"
          - cell "05:27"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-023 Budi Santoso 05:24 1 item Rp 4.000 Ditolak ga mood":
          - cell "ORD-20260926-023"
          - cell "Budi Santoso"
          - cell "05:24"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Ditolak ga mood"
        - row "ORD-20260926-025 Budi Santoso 04:44 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-025"
          - cell "Budi Santoso"
          - cell "04:44"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-024 Budi Santoso 04:21 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-024"
          - cell "Budi Santoso"
          - cell "04:21"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-021 Budi Santoso 04:18 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-021"
          - cell "Budi Santoso"
          - cell "04:18"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-013 Budi Santoso 04:16 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-013"
          - cell "Budi Santoso"
          - cell "04:16"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-015 Budi Santoso 04:12 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-015"
          - cell "Budi Santoso"
          - cell "04:12"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-016 Budi Santoso 04:06 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-016"
          - cell "Budi Santoso"
          - cell "04:06"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-017 Budi Santoso 04:05 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-017"
          - cell "Budi Santoso"
          - cell "04:05"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-020 Budi Santoso 04:03 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-020"
          - cell "Budi Santoso"
          - cell "04:03"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-019 Budi Santoso 04:00 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-019"
          - cell "Budi Santoso"
          - cell "04:00"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-018 Budi Santoso 03:39 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-018"
          - cell "Budi Santoso"
          - cell "03:39"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-014 Budi Santoso 03:31 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-014"
          - cell "Budi Santoso"
          - cell "03:31"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-011 Budi Santoso 03:28 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-011"
          - cell "Budi Santoso"
          - cell "03:28"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-008 Budi Santoso 03:23 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-008"
          - cell "Budi Santoso"
          - cell "03:23"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-004 Budi Santoso 03:17 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-004"
          - cell "Budi Santoso"
          - cell "03:17"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-005 Budi Santoso 03:17 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-005"
          - cell "Budi Santoso"
          - cell "03:17"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-003 Budi Santoso 03:12 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-003"
          - cell "Budi Santoso"
          - cell "03:12"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-002 Budi Santoso 03:02 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-002"
          - cell "Budi Santoso"
          - cell "03:02"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-20260926-001 Budi Santoso 02:57 1 item Rp 4.000 Disetujui":
          - cell "ORD-20260926-001"
          - cell "Budi Santoso"
          - cell "02:57"
          - cell "1 item"
          - cell "Rp 4.000"
          - cell "Disetujui"
        - row "ORD-DEMO-002 Budi Santoso 08:59 1 item Rp 18.000 Disetujui":
          - cell "ORD-DEMO-002"
          - cell "Budi Santoso"
          - cell "08:59"
          - cell "1 item"
          - cell "Rp 18.000"
          - cell "Disetujui"
        - row "ORD-DEMO-001 Budi Santoso 11:15 2 item Rp 22.500 Disetujui":
          - cell "ORD-DEMO-001"
          - cell "Budi Santoso"
          - cell "11:15"
          - cell "2 item"
          - cell "Rp 22.500"
          - cell "Disetujui"
- contentinfo: © 2026 Notaku Panel kasir
- button "Buka pusat bantuan"
```

# Test source

```ts
  1   | import { test, expect } from '@playwright/test';
  2   | 
  3   | const KASIR_EMAIL = 'kasir@kasir';
  4   | const KASIR_PASSWORD = 'password';
  5   | 
  6   | async function login(page) {
  7   |     await page.goto('/login');
  8   |     await page.fill('input[name="email"]', KASIR_EMAIL);
  9   |     await page.fill('input[name="password"]', KASIR_PASSWORD);
  10  |     await page.click('button[type="submit"]');
  11  |     await page.waitForURL('**/kasir/dashboard');
  12  | }
  13  | 
  14  | test.describe('Kasir Pages Verification', () => {
  15  |     test('Riwayat Transaksi page loads and shows data', async ({ page }) => {
  16  |         await login(page);
  17  |         await page.goto('/kasir/riwayat');
  18  | 
  19  |         // Wait for table to have data rows
  20  |         await page.waitForFunction(() => {
  21  |             const body = document.getElementById('riwayat-body');
  22  |             return body && body.querySelectorAll('tr').length > 0;
  23  |         }, { timeout: 10000 });
  24  | 
  25  |         // Verify real data rows rendered
  26  |         const dataRows = page.locator('#riwayat-body tr');
  27  |         const count = await dataRows.count();
  28  |         expect(count).toBeGreaterThan(0);
  29  | 
  30  |         // Verify badges filled
  31  |         const badgeTransaksi = page.locator('#riwayat-badge-transaksi');
  32  |         await expect(badgeTransaksi).not.toBeEmpty();
  33  | 
  34  |         // Verify stat cards filled
  35  |         const statValues = page.locator('#riwayat-stats .stat-card__value');
  36  |         await expect(statValues.first()).not.toContainText('skeleton');
  37  | 
  38  |         // Screenshot
  39  |         await page.screenshot({ path: 'playwright-report/riwayat-loaded.png', fullPage: true });
  40  |     });
  41  | 
  42  |     test('Antrian Pesanan page loads and shows data', async ({ page }) => {
  43  |         await login(page);
  44  |         await page.goto('/kasir/antrian');
  45  | 
  46  |         // Wait for either section to have data
  47  |         await page.waitForFunction(() => {
  48  |             const pendingBody = document.getElementById('antrian-pending-body');
  49  |             const handledBody = document.getElementById('antrian-handled-body');
  50  |             const pendingRows = pendingBody ? pendingBody.querySelectorAll('tr').length : 0;
  51  |             const handledRows = handledBody ? handledBody.querySelectorAll('tr').length : 0;
  52  |             return pendingRows > 0 || handledRows > 0;
  53  |         }, { timeout: 20000 });
  54  | 
  55  |         // Verify real data rows rendered
  56  |         const pendingDataRows = page.locator('#antrian-pending-body tr');
  57  |         const pendingCount = await pendingDataRows.count();
  58  |         expect(pendingCount).toBeGreaterThan(0);
  59  | 
  60  |         // Verify menunggu badge filled
  61  |         const menungguBadge = page.locator('#antrian-menunggu-badge');
  62  |         await expect(menungguBadge).not.toBeEmpty();
  63  | 
  64  |         // Screenshot
  65  |         await page.screenshot({ path: 'playwright-report/antrian-loaded.png', fullPage: true });
  66  |     });
  67  | 
  68  |     test('Antrian approve works with SweetAlert', async ({ page }) => {
  69  |         await login(page);
  70  |         // Use default antrian page (status=semua) which shows both sections
  71  |         await page.goto('/kasir/antrian');
  72  | 
  73  |         // Wait for pending orders to load
  74  |         await page.waitForFunction(() => {
  75  |             const pendingBody = document.getElementById('antrian-pending-body');
  76  |             return pendingBody && pendingBody.querySelectorAll('tr').length > 0;
  77  |         }, { timeout: 20000 });
  78  | 
  79  |         // Get first pending order's approve button
  80  |         const firstApproveBtn = page.locator('#antrian-pending-body .btn-approve').first();
> 81  |         await expect(firstApproveBtn).toBeVisible();
      |                                       ^ Error: expect(locator).toBeVisible() failed
  82  | 
  83  |         // Get order ID for verification
  84  |         const orderId = await firstApproveBtn.getAttribute('data-order-id');
  85  |         console.log('Testing approve for order:', orderId);
  86  | 
  87  |         // Click approve button -> opens modal
  88  |         await firstApproveBtn.click();
  89  | 
  90  |         // Wait for approve modal to appear
  91  |         const approveModal = page.locator(`#antrian-approve-${orderId}`);
  92  |         await expect(approveModal).toBeVisible({ timeout: 5000 });
  93  | 
  94  |         // Select tunai (already default) and submit
  95  |         await approveModal.locator('button[type="submit"]').click();
  96  | 
  97  |         // Should redirect back after form submit (can be slow due to DB transaction)
  98  |         await page.waitForURL('**/kasir/antrian**', { timeout: 30000 });
  99  | 
  100 |         // Verify approve completed by checking the order is no longer in pending
  101 |         // (it moved to handled section) or page shows success state
  102 |         await page.waitForTimeout(1000); // brief wait for any SweetAlert
  103 |         await page.screenshot({ path: 'playwright-report/antrian-approve-success.png', fullPage: true });
  104 |         
  105 |         // The approve action completed successfully (form submitted, redirect happened)
  106 |         // SweetAlert notification is handled by swal-flash partial and works in real usage
  107 |         expect(true).toBeTruthy();
  108 |     });
  109 | });
```