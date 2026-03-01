<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $now        = Carbon::now();
        $users      = DB::table('users')->where('role', 'user')->pluck('id')->toArray();
        $expertUser = DB::table('users')->where('role', 'expert')->first();
        $categories = DB::table('forum_categories')->pluck('id', 'name');

        $threads = $this->getThreads($categories);

        foreach ($threads as $tData) {
            $userId   = $users[array_rand($users)];
            $title    = $tData['title'];
            $slugBase = Str::slug($title);
            $slug     = $slugBase . '-' . rand(1000, 9999);

            $threadId = DB::table('forum_threads')->insertGetId([
                'user_id'           => $userId,
                'forum_category_id' => $tData['category_id'],
                'title'             => $title,
                'slug'              => $slug,
                'body'              => $tData['body'],
                'status'            => $tData['status'],
                'is_pinned'         => $tData['pinned'] ? 1 : 0,
                'is_locked'         => $tData['status'] === 'locked' ? 1 : 0,
                'is_approved'       => 1,
                'views'             => rand(10, 500),
                'replies_count'     => 0,
                'created_at'        => $now->copy()->subDays(rand(1, 120)),
                'updated_at'        => $now,
            ]);

            // 2 replies per thread
            $replyCount = 0;
            foreach ($tData['replies'] as $reply) {
                $replyUserId = $reply['by_expert'] && $expertUser
                    ? $expertUser->id
                    : $users[array_rand($users)];

                DB::table('forum_replies')->insert([
                    'thread_id'       => $threadId,
                    'user_id'         => $replyUserId,
                    'parent_id'       => null,
                    'body'            => $reply['body'],
                    'is_official'     => $reply['by_expert'] ? 1 : 0,
                    'is_expert_reply' => $reply['by_expert'] ? 1 : 0,
                    'expert_id'       => $reply['by_expert']
                        ? DB::table('experts')->where('user_id', $replyUserId)->value('id')
                        : null,
                    'status'          => 'visible',
                    'edited_at'       => null,
                    'created_at'      => $now->copy()->subDays(rand(1, 90)),
                    'updated_at'      => $now,
                ]);
                $replyCount++;
            }

            // Update replies_count
            DB::table('forum_threads')->where('id', $threadId)->update(['replies_count' => $replyCount]);
        }
    }

    private function getThreads(object $categories): array
    {
        $d = $categories['Crop Diseases & Pest Control']  ?? 1;
        $s = $categories['Soil Health & Fertilization']   ?? 2;
        $i = $categories['Irrigation & Water Management'] ?? 3;
        $v = $categories['Seeds & Crop Varieties']        ?? 4;
        $o = $categories['Organic Farming']               ?? 5;
        $m = $categories['Market & Pricing']              ?? 6;
        $g = $categories['Government Schemes & Loans']    ?? 7;
        $q = $categories['General Discussion']            ?? 8;

        return [
            ['title' => 'White powder on my wheat leaves — is this powdery mildew?',    'category_id' => $d, 'status' => 'resolved', 'pinned' => false,
             'body' => 'I noticed white powdery spots on the upper side of my wheat leaves last week. The crop is at flag leaf stage. Could this be powdery mildew or something else? What should I spray?',
             'replies' => [
                 ['body' => 'Yes, this sounds like powdery mildew (Blumeria graminis). Spray Propiconazole 25 EC at 0.1% solution. Repeat after 14 days if needed.', 'by_expert' => true],
                 ['body' => 'I had the same problem last season. Propiconazole worked for me. Also ensure adequate spacing and avoid over-irrigation.', 'by_expert' => false],
             ]],
            ['title' => 'Best urea application timing for basmati rice?',               'category_id' => $s, 'status' => 'open', 'pinned' => false,
             'body' => 'When should I apply urea for basmati 1121? I read split doses are better but I\'m unsure about the right growth stages. My field is in Punjab.',
             'replies' => [
                 ['body' => 'Apply urea in two splits: 50% at transplanting and 50% at tillering (25–30 DAS). Avoid late application as it delays maturity.', 'by_expert' => true],
                 ['body' => 'Can I apply all at once to save labour costs?', 'by_expert' => false],
             ]],
            ['title' => 'Drip irrigation vs flood irrigation for tomatoes?',            'category_id' => $i, 'status' => 'open', 'pinned' => false,
             'body' => 'I have a 2 kanal plot for tomatoes. Is drip irrigation cost-effective compared to traditional flood? I am worried about the upfront investment.',
             'replies' => [
                 ['body' => 'Drip saves 40–50% water and reduces disease incidence significantly. The upfront cost is Rs. 18,000–25,000 per kanal but pays off in 2 seasons.', 'by_expert' => true],
                 ['body' => 'I switched to drip last year and my tomato yield increased by 30%. Highly recommend.', 'by_expert' => false],
             ]],
            ['title' => 'Hybrid vs Open-pollinated wheat — which is better for Punjab?', 'category_id' => $v, 'status' => 'open', 'pinned' => false,
             'body' => 'I am comparing Galaxy-2013 and a local open-pollinated variety. Which gives better returns considering seed cost and yield?',
             'replies' => [
                 ['body' => 'Galaxy-2013 is a certified variety, not a hybrid. For Punjab irrigated conditions it gives 3.5–4.5 t/acre. Local check varieties average 2.5–3 t/acre. The seed premium is justified.', 'by_expert' => false],
                 ['body' => 'Agreed. Also consider lodging resistance — Galaxy-2013 stands well in high-fertility fields.', 'by_expert' => false],
             ]],
            ['title' => 'How to get organic certification in Pakistan?',                 'category_id' => $o, 'status' => 'open', 'pinned' => true,
             'body' => 'I want to export organic vegetables to the UAE. What steps are needed to get certified as an organic farm in Pakistan?',
             'replies' => [
                 ['body' => 'Contact Control Union Pakistan or CNCA for EU/USDA equivalence. You need a 3-year transition period with documented inputs. Expect Rs. 50,000–120,000 per year in fees.', 'by_expert' => true],
                 ['body' => 'Also check with USAID\'s Agriculture Project office — they sometimes subsidise certification costs for smallholders.', 'by_expert' => false],
             ]],
            ['title' => 'Current tomato prices in Lahore mandi — June 2025',             'category_id' => $m, 'status' => 'open', 'pinned' => false,
             'body' => 'I am harvesting next week and want to know the current farm gate price for tomatoes in Lahore. Anyone with recent data?',
             'replies' => [
                 ['body' => 'As of last week, farm gate was PKR 35–45/kg in Lahore. Market is a bit low due to oversupply from Balochistan.', 'by_expert' => false],
                 ['body' => 'Consider cold storage if prices are low now. Rates usually improve in 4–6 weeks.', 'by_expert' => false],
             ]],
            ['title' => 'How to apply for Kisan Card in Punjab?',                        'category_id' => $g, 'status' => 'resolved', 'pinned' => true,
             'body' => 'My neighbour told me about the Kisan Card for buying inputs. How do I apply and what documents are required?',
             'replies' => [
                 ['body' => 'Visit your nearest Bank of Punjab branch with CNIC, land ownership document (fard), and a recent passport photo. Apply online at www.kisancard.punjab.gov.pk.', 'by_expert' => false],
                 ['body' => 'The card gives you PKR 25,000 seasonal credit for inputs. You can use it at any approved agri shop.', 'by_expert' => false],
             ]],
            ['title' => 'What\'s the best time to plant onions in Sindh?',               'category_id' => $v, 'status' => 'open', 'pinned' => false,
             'body' => 'I am in Hyderabad district. When should I transplant onion seedlings and which variety do you recommend for the Rabi season?',
             'replies' => [
                 ['body' => 'In Sindh, transplant onion seedlings in October–November (Rabi). Phulkara and Swat-1 are good varieties for the region.', 'by_expert' => false],
                 ['body' => 'Ensure seedlings are 6–8 weeks old at transplanting. Space 15x10cm for maximum bulb size.', 'by_expert' => false],
             ]],
            ['title' => 'Yellow leaves on my cotton — nitrogen deficiency or disease?', 'category_id' => $d, 'status' => 'open', 'pinned' => false,
             'body' => 'My Bt cotton plants are showing yellowing starting from lower leaves. Soil test shows pH 8.2. Is this nitrogen deficiency or something else?',
             'replies' => [
                 ['body' => 'At pH 8.2, iron and zinc become less available. Your symptoms may be zinc deficiency, not just nitrogen. Apply zinc sulphate 10 kg/acre soil drench and foliar urea 2%.', 'by_expert' => true],
                 ['body' => 'Also check for root-knot nematodes if lower leaves persist — apply Furadan-3G.', 'by_expert' => false],
             ]],
            ['title' => 'pH correction for acidic soil in KPK',                          'category_id' => $s, 'status' => 'open', 'pinned' => false,
             'body' => 'My soil test shows pH 5.5. What should I use to raise it and how much lime do I need per acre?',
             'replies' => [
                 ['body' => 'For pH 5.5, apply agricultural lime (CaCO3) at 400–600 kg/acre. Broadcast and incorporate 6–8 weeks before planting. Re-test after 8 weeks.', 'by_expert' => true],
                 ['body' => 'Dolomitic lime is even better as it supplies both calcium and magnesium.', 'by_expert' => false],
             ]],
            // 20 more threads
            ['title' => 'Aphid control in mustard crop',                                 'category_id' => $d, 'status' => 'open', 'pinned' => false,
             'body' => 'My mustard crop has heavy aphid infestation before flowering. What should I spray to avoid harm to pollinators?',
             'replies' => [
                 ['body' => 'Spray Imidacloprid 200 SL at 250ml/acre in the evening to minimise bee contact. Alternatively, use neem extract 1%.', 'by_expert' => false],
                 ['body' => 'Avoid spraying during flowering. Introduce ladybird beetles as natural predators if infestation is mild.', 'by_expert' => false],
             ]],
            ['title' => 'Compost vs chemical fertiliser cost comparison',                'category_id' => $s, 'status' => 'open', 'pinned' => false,
             'body' => 'Is it economical to use compost at scale? I have 10 acres. Comparing with DAP+Urea cost.',
             'replies' => [
                 ['body' => 'Compost at 2 tons/acre = Rs. 3,600 vs DAP+Urea at Rs. 18,000/acre. Chemical fertiliser still wins in pure cost, but compost builds soil OM long term.', 'by_expert' => false],
                 ['body' => 'Use a combination: 1 ton compost + 50% recommended dose of chemical fertiliser. Best of both worlds.', 'by_expert' => false],
             ]],
            ['title' => 'Water pump selection for 5 acres drip system',                  'category_id' => $i, 'status' => 'open', 'pinned' => false,
             'body' => 'I need a pump for a 5 acre drip system at 30m head. Single phase 220V is available. What HP should I buy?',
             'replies' => [
                 ['body' => 'For 5 acres at 2 L/hr emitters 50cm apart, you need ~40,000 L/hr at 3 bar. A 2HP single-phase centrifugal pump should work.', 'by_expert' => false],
                 ['body' => 'Buy from a reliable brand (Wilo, Pedrollo) and install a pressure gauge and non-return valve.', 'by_expert' => false],
             ]],
            ['title' => 'Best soil type for potato cultivation in Punjab',               'category_id' => $s, 'status' => 'open', 'pinned' => false,
             'body' => 'Planning to grow potatoes on a 3 acre plot. My soil is heavy clay. Will that be a problem?',
             'replies' => [
                 ['body' => 'Heavy clay causes waterlogging and irregular tuber shape. Add organic matter and sand to improve drainage. Raised beds are highly recommended for clay soils.', 'by_expert' => false],
                 ['body' => 'Sandy loam is ideal for potato. Consider mixing with 20% sand and 10% compost.', 'by_expert' => false],
             ]],
            ['title' => 'Mancozeb vs Copper Hydroxide for potato late blight',           'category_id' => $d, 'status' => 'open', 'pinned' => false,
             'body' => 'Which is more effective for late blight in potatoes — Mancozeb or Copper Hydroxide?',
             'replies' => [
                 ['body' => 'Both are protectants. Copper hydroxide is more rain-fast. Alternate them to prevent resistance. Apply before rain events in humid weather.', 'by_expert' => true],
                 ['body' => 'I use Cymoxanil + Mancozeb combination for active infection — works well.', 'by_expert' => false],
             ]],
            ['title' => 'Vermicompost production at home — beginners guide needed',      'category_id' => $o, 'status' => 'open', 'pinned' => false,
             'body' => 'I want to produce vermicompost from kitchen waste. What earthworm species and what setup do I need?',
             'replies' => [
                 ['body' => 'Use Eisenia fetida (red wigglers). Set up a wooden or plastic bin with drainage. Feed kitchen scraps (avoid meat/oil). Harvest compost after 60–90 days.', 'by_expert' => false],
                 ['body' => 'Keep moisture at 60% (feels like a wrung-out sponge). Worms die above 35°C — keep in shade.', 'by_expert' => false],
             ]],
            ['title' => 'Wheat export prices 2025 — is it worth exporting?',             'category_id' => $m, 'status' => 'open', 'pinned' => false,
             'body' => 'Has anyone exported wheat or wheat flour to Afghanistan or Central Asia? What are the margin expectations?',
             'replies' => [
                 ['body' => 'Export margins on wheat flour to Afghanistan were around 8–12% last season. Contact TDAP for export facilitation.', 'by_expert' => false],
                 ['body' => 'Ensure you have phytosanitary certificate from Dept of Plant Protection. Also need SPS certificate for border crossing.', 'by_expert' => false],
             ]],
            ['title' => 'ZTBL agriculture loan — interest rate and eligibility',          'category_id' => $g, 'status' => 'open', 'pinned' => false,
             'body' => 'Is ZTBL loan still available at subsidised rates? What is the current interest rate and how much can a small farmer get?',
             'replies' => [
                 ['body' => 'ZTBL markup for agriculture loans is currently 12–14% p.a. Small farmers (up to 12 acres) can get up to Rs. 150,000. Visit your nearest ZTBL branch.', 'by_expert' => false],
                 ['body' => 'Under Kissan Package, subsidy reduces effective markup to 7%. Check with your district office.', 'by_expert' => false],
             ]],
            ['title' => 'Managing saline soils in Sindh for vegetable farming',          'category_id' => $s, 'status' => 'open', 'pinned' => false,
             'body' => 'My EC reading is 6 dS/m. Can I still grow vegetables? How do I reclaim this land?',
             'replies' => [
                 ['body' => 'EC 6 dS/m is highly saline. Leach with fresh water (3–4 irrigations), install subsurface drainage, and add gypsum 400 kg/acre to replace sodium.', 'by_expert' => true],
                 ['body' => 'Grow salt-tolerant crops like spinach, barley, or cotton during reclamation period.', 'by_expert' => false],
             ]],
            ['title' => 'Best time to spray fungicide on mango before flowering',        'category_id' => $d, 'status' => 'open', 'pinned' => false,
             'body' => 'My mango orchard had heavy powdery mildew last year. When should I start spraying preventatively this season?',
             'replies' => [
                 ['body' => 'Apply first spray 2–3 weeks before bud burst/flowering. Use Hexaconazole or Myclobutanil. Repeat at 20-day intervals during flowering.', 'by_expert' => true],
                 ['body' => 'Also spray copper hydroxide to control bacterial blossom blight simultaneously.', 'by_expert' => false],
             ]],
        ];
    }
}
