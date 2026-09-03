<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class TourAdvisory extends BaseConfig
{
    /**
     * Advisory rules are matched against tour title, overview, route stops,
     * attraction names and itinerary summaries after Vietnamese accent folding.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $categories = [
        'multi_destination' => [
            'condition' => 'many_stops',
            'priority' => 90,
            'strength' => [
                'vi' => 'Kết hợp nhiều điểm đến trong một hành trình có sẵn, phù hợp khách muốn tối ưu thời gian.',
                'en' => 'Combines several destinations in one ready itinerary, suitable for guests who want to optimize travel time.',
            ],
            'suitable_for' => [
                'vi' => ['nhóm bạn', 'company trip', 'khách thích tham quan nhiều điểm'],
                'en' => ['friend groups', 'company trips', 'sightseeing-focused guests'],
            ],
        ],
        'short_break' => [
            'condition' => 'short_trip',
            'priority' => 85,
            'strength' => [
                'vi' => 'Thời lượng gọn, phù hợp khách muốn đi nhanh trong kỳ nghỉ ngắn hoặc cuối tuần.',
                'en' => 'Compact duration, suitable for short holidays or weekend-style trips.',
            ],
            'suitable_for' => [
                'vi' => ['nhóm nhỏ', 'gia đình', 'khách bận rộn'],
                'en' => ['small groups', 'families', 'busy travelers'],
            ],
        ],
        'beach_resort' => [
            'keywords' => ['biển', 'beach', 'nha trang', 'phú quốc', 'đà nẵng', 'mỹ khê', 'bãi dài', 'vịnh', 'hòn', 'resort'],
            'priority' => 80,
            'strength' => [
                'vi' => 'Có yếu tố biển/nghỉ dưỡng, dễ tư vấn cho khách muốn đi thư giãn hoặc đi cùng gia đình.',
                'en' => 'Includes a beach or resort angle, suitable for guests who want a relaxed leisure trip or a family vacation.',
            ],
            'suitable_for' => [
                'vi' => ['gia đình', 'nhóm bạn', 'khách muốn nghỉ dưỡng'],
                'en' => ['families', 'friend groups', 'leisure guests'],
            ],
        ],
        'mountain_retreat' => [
            'keywords' => ['đà lạt', 'sa pa', 'sapa', 'fansipan', 'langbiang', 'cao nguyên', 'săn mây', 'khí hậu mát', 'núi'],
            'priority' => 78,
            'strength' => [
                'vi' => 'Không khí mát và nhiều cảnh quan, hợp khách muốn đổi gió, nghỉ nhẹ và chụp hình.',
                'en' => 'Cooler climate and scenic landscapes, suitable for guests who want a refreshing leisure trip.',
            ],
            'suitable_for' => [
                'vi' => ['cặp đôi', 'gia đình', 'nhóm bạn thích chụp hình'],
                'en' => ['couples', 'families', 'photo-oriented friend groups'],
            ],
        ],
        'heritage_culture' => [
            'keywords' => ['di sản', 'hội an', 'huế', 'cố đô', 'phố cổ', 'thành cổ', 'chùa', 'đền', 'cung điện', 'heritage', 'bảo tàng'],
            'priority' => 75,
            'strength' => [
                'vi' => 'Nổi bật về văn hóa, di sản và tham quan, hợp khách muốn chuyến đi có chiều sâu trải nghiệm.',
                'en' => 'Strong cultural and heritage angle, good for guests who want a deeper sightseeing experience.',
            ],
            'suitable_for' => [
                'vi' => ['khách thích tham quan', 'gia đình', 'nhóm khách lớn tuổi'],
                'en' => ['sightseeing-focused guests', 'families', 'senior guests'],
            ],
        ],
        'nature_landscape' => [
            'keywords' => ['động', 'thiên đường', 'hang', 'núi', 'thác', 'vườn quốc gia', 'hồ', 'sông', 'island', 'nature', 'mountain', 'cao nguyên'],
            'priority' => 70,
            'strength' => [
                'vi' => 'Có nhiều cảnh quan thiên nhiên, phù hợp khách muốn trải nghiệm ngoài phố thị.',
                'en' => 'Adds nature and landscape experiences beyond city sightseeing.',
            ],
            'suitable_for' => [
                'vi' => ['khách thích thiên nhiên', 'nhóm bạn', 'khách thích chụp hình'],
                'en' => ['nature lovers', 'friend groups', 'photo-oriented guests'],
            ],
        ],
        'east_asia' => [
            'keywords' => ['nhật bản', 'japan', 'tokyo', 'osaka', 'kyoto', 'hàn quốc', 'korea', 'seoul', 'nami', 'trung quốc', 'china', 'thượng hải', 'bắc kinh'],
            'priority' => 69,
            'strength' => [
                'vi' => 'Tuyến Đông Bắc Á dễ tư vấn theo mùa, trải nghiệm văn hóa, ẩm thực và mua sắm rõ ràng.',
                'en' => 'East Asia route with clear seasonal, cultural, food and shopping angles.',
            ],
            'suitable_for' => [
                'vi' => ['khách đi lần đầu', 'gia đình', 'nhóm bạn'],
                'en' => ['first-time outbound travelers', 'families', 'friend groups'],
            ],
        ],
        'family_fun' => [
            'keywords' => ['bà nà', 'cầu vàng', 'vinwonders', 'safari', 'disney', 'universal', 'sentosa', 'công viên', 'khu vui chơi'],
            'priority' => 68,
            'strength' => [
                'vi' => 'Có điểm vui chơi dễ nhận biết, phù hợp nhóm gia đình hoặc khách đi cùng trẻ em.',
                'en' => 'Has recognizable family-friendly attractions, suitable for families or guests traveling with children.',
            ],
            'suitable_for' => [
                'vi' => ['gia đình', 'trẻ em', 'nhóm bạn trẻ'],
                'en' => ['families', 'children', 'younger groups'],
            ],
        ],
        'southeast_asia' => [
            'keywords' => ['thái lan', 'thailand', 'bangkok', 'pattaya', 'singapore', 'malaysia', 'bali', 'indonesia'],
            'priority' => 62,
            'strength' => [
                'vi' => 'Tuyến Đông Nam Á dễ đi, thời gian bay vừa phải và phù hợp khách muốn tour nước ngoài nhẹ ngân sách.',
                'en' => 'Southeast Asia routes are easy to access, with moderate flight time and approachable outbound budgets.',
            ],
            'suitable_for' => [
                'vi' => ['khách đi nước ngoài lần đầu', 'gia đình', 'nhóm bạn'],
                'en' => ['first-time outbound travelers', 'families', 'friend groups'],
            ],
        ],
        'europe_longhaul' => [
            'keywords' => ['châu âu', 'europe', 'pháp', 'france', 'ý', 'italy', 'thụy sĩ', 'switzerland', 'đức', 'germany', 'tây ban nha', 'spain', 'thổ nhĩ kỳ', 'turkey'],
            'priority' => 60,
            'strength' => [
                'vi' => 'Tuyến xa cần chuẩn bị kỹ về visa, lịch bay và sức khỏe, phù hợp khách muốn hành trình trải nghiệm cao cấp hơn.',
                'en' => 'Long-haul route that needs careful visa, flight and health preparation, suitable for guests seeking a higher-value experience.',
            ],
            'suitable_for' => [
                'vi' => ['khách có ngân sách tốt', 'khách thích văn hóa', 'nhóm gia đình người lớn'],
                'en' => ['higher-budget guests', 'culture-focused travelers', 'adult family groups'],
            ],
        ],
        'long_itinerary' => [
            'condition' => 'long_trip',
            'priority' => 58,
            'strength' => [
                'vi' => 'Thời lượng dài, phù hợp khách muốn đi sâu hơn thay vì chỉ ghé nhanh các điểm chính.',
                'en' => 'Longer duration, suitable for guests who want a deeper trip instead of only quick headline stops.',
            ],
            'suitable_for' => [
                'vi' => ['khách có thời gian', 'khách thích khám phá kỹ', 'nhóm gia đình người lớn'],
                'en' => ['guests with more travel time', 'deep-exploration travelers', 'adult family groups'],
            ],
        ],
        'shopping_food' => [
            'keywords' => ['shopping', 'mua sắm', 'chợ đêm', 'chợ hàn', 'mall', 'outlet', 'ẩm thực', 'đặc sản'],
            'priority' => 55,
            'strength' => [
                'vi' => 'Có thời gian mua sắm/ẩm thực, dễ tư vấn cho khách thích trải nghiệm nhẹ và mua đặc sản.',
                'en' => 'Includes shopping or food time, useful for guests who prefer lighter experiences and local specialties.',
            ],
            'suitable_for' => [
                'vi' => ['nhóm bạn', 'gia đình', 'khách muốn lịch trình nhẹ'],
                'en' => ['friend groups', 'families', 'guests who prefer a lighter pace'],
            ],
        ],
        'city_break' => [
            'keywords' => ['thành phố', 'city tour', 'seoul', 'tokyo', 'bangkok', 'singapore', 'paris'],
            'priority' => 45,
            'strength' => [
                'vi' => 'Tập trung vào điểm đến đô thị, thuận tiện cho khách muốn lịch trình gọn, dễ di chuyển.',
                'en' => 'Focuses on urban destinations, convenient for guests who want a compact and easy-to-move itinerary.',
            ],
            'suitable_for' => [
                'vi' => ['khách đi lần đầu', 'nhóm nhỏ', 'khách thích city tour'],
                'en' => ['first-time guests', 'small groups', 'city tour guests'],
            ],
        ],
    ];

    /**
     * These rules read the customer question, not the tour content.
     * They help the chatbox personalize advice without manually writing notes for each tour.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $requestSignals = [
        'family' => [
            'keywords' => ['gia đình', 'tre em', 'trẻ em', 'con nhỏ', 'em bé', 'ba mẹ', 'bố mẹ'],
            'note' => [
                'vi' => 'Khách đi gia đình nên kiểm tra độ tuổi trẻ em/người lớn tuổi, nhu cầu phòng gần nhau và nhịp đi nhẹ.',
                'en' => 'For family trips, check children/senior ages, room proximity and preferred pace.',
            ],
            'questions' => [
                'vi' => ['Trong đoàn có trẻ em hoặc người lớn tuổi không?', 'Gia đình muốn phòng gần nhau hay cần phòng family?'],
                'en' => ['Are there children or senior guests in the group?', 'Do you need nearby rooms or family rooms?'],
            ],
        ],
        'children' => [
            'keywords' => ['trẻ em', 'tre em', 'em bé', 'con nhỏ', 'bé', 'kids', 'children'],
            'note' => [
                'vi' => 'Có trẻ em thì nên ưu tiên lịch trình không quá dày, giờ bay thuận tiện và khách sạn dễ di chuyển.',
                'en' => 'For children, prioritize a lighter pace, convenient flight times and easy hotel access.',
            ],
            'questions' => [
                'vi' => ['Bé bao nhiêu tuổi để kiểm tra giá trẻ em và lịch trình phù hợp?', 'Gia đình có cần phòng family hoặc giường phụ không?'],
                'en' => ['How old are the children for child pricing and itinerary fit?', 'Do you need family rooms or extra beds?'],
            ],
        ],
        'senior' => [
            'keywords' => ['người lớn tuổi', 'nguoi lon tuoi', 'ba mẹ', 'bố mẹ', 'ông bà', 'cao tuổi', 'sức khỏe'],
            'note' => [
                'vi' => 'Có người lớn tuổi thì nên tránh lịch trình quá dày, nhiều bậc thang hoặc đổi khách sạn liên tục.',
                'en' => 'For senior guests, avoid overly dense itineraries, too many stairs or frequent hotel changes.',
            ],
            'questions' => [
                'vi' => ['Trong đoàn có ai cần hạn chế đi bộ hoặc leo bậc thang không?', 'Anh/chị muốn lịch trình nhẹ hơn hay vẫn ưu tiên tham quan nhiều điểm?'],
                'en' => ['Does anyone need to limit walking or stairs?', 'Would you prefer a lighter pace or more sightseeing?'],
            ],
        ],
        'couple' => [
            'keywords' => ['cặp đôi', 'cap doi', 'honeymoon', 'tuần trăng mật', 'trăng mật', 'kỷ niệm'],
            'note' => [
                'vi' => 'Khách cặp đôi nên ưu tiên khách sạn đẹp, thời gian riêng tư và trải nghiệm ăn tối/view đẹp nếu ngân sách cho phép.',
                'en' => 'For couples, prioritize nicer hotels, private time and scenic dining or experiences if budget allows.',
            ],
            'questions' => [
                'vi' => ['Chuyến đi thiên về nghỉ dưỡng hay chụp hình/trải nghiệm?', 'Có cần gợi ý khách sạn hoặc trải nghiệm riêng cho cặp đôi không?'],
                'en' => ['Is the trip more for relaxation or photo/experience-focused?', 'Do you need hotel or private experience suggestions for couples?'],
            ],
        ],
        'company' => [
            'keywords' => ['công ty', 'company', 'doanh nghiệp', 'team building', 'company trip', 'gala', 'kick off', 'nhân viên'],
            'note' => [
                'vi' => 'Khách công ty nên hỏi mục tiêu chuyến đi, ngân sách duyệt, số phòng, nhu cầu team building/gala và người chốt brief.',
                'en' => 'For company trips, ask about objective, approved budget, rooming, team building/gala needs and brief owner.',
            ],
            'questions' => [
                'vi' => ['Chuyến đi có cần team building hoặc gala dinner không?', 'Công ty đã có ngân sách duyệt theo đầu khách chưa?'],
                'en' => ['Do you need team building or gala dinner?', 'Is there an approved budget per guest?'],
            ],
        ],
        'holiday_peak' => [
            'keywords' => ['lễ', '2/9', '30/4', '1/5', 'tết', 'tet', 'noel', 'giáng sinh', 'hè', 'cao điểm'],
            'note' => [
                'vi' => 'Khách đi mùa lễ/cao điểm nên chốt ngày sớm vì giá vé bay, phòng và chỗ tour có thể thay đổi nhanh.',
                'en' => 'For holiday or peak-season trips, confirm dates early because flights, rooms and tour seats can change quickly.',
            ],
            'questions' => [
                'vi' => ['Ngày đi có linh hoạt được không?', 'Khách đã cần giữ vé/phòng ngay nếu còn chỗ phù hợp chưa?'],
                'en' => ['Are the travel dates flexible?', 'Should the team hold flights/rooms if suitable seats are still available?'],
            ],
        ],
        'budget_sensitive' => [
            'keywords' => ['ngân sách', 'budget', 'tiết kiệm', 'giá tốt', 'rẻ', 'khoảng', 'tầm', 'triệu', 'tr', '5tr', '10tr'],
            'note' => [
                'vi' => 'Khách có ngân sách rõ nên hỏi ngân sách đã gồm vé máy bay chưa và mức khách sạn mong muốn.',
                'en' => 'When budget is mentioned, check whether flights are included and what hotel level is expected.',
            ],
            'questions' => [
                'vi' => ['Ngân sách này đã gồm vé máy bay chưa?', 'Khách muốn khách sạn tiêu chuẩn mấy sao?'],
                'en' => ['Does this budget include flights?', 'What hotel standard do you prefer?'],
            ],
        ],
        'relaxed' => [
            'keywords' => ['nghỉ dưỡng', 'thư giãn', 'nhẹ nhàng', 'không đi nhiều', 'resort', 'biển'],
            'note' => [
                'vi' => 'Khách thiên về nghỉ dưỡng nên ưu tiên lịch trình ít đổi khách sạn, có thời gian tự do và resort/khách sạn đúng khu vực.',
                'en' => 'For leisure-focused guests, prioritize fewer hotel changes, free time and the right resort or hotel area.',
            ],
            'questions' => [
                'vi' => ['Khách muốn nghỉ gần biển/trung tâm hay resort riêng tư?', 'Có muốn giảm bớt điểm tham quan để nghỉ nhiều hơn không?'],
                'en' => ['Do you prefer beach/central hotels or a more private resort?', 'Would you like fewer sightseeing stops for more rest time?'],
            ],
        ],
    ];

    /**
     * Destination guides provide specific advisory notes for popular routes.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $destinationGuides = [
        'da_nang' => [
            'keywords' => ['đà nẵng', 'danang', 'mỹ khê', 'bà nà', 'cầu vàng', 'hội an', 'ngũ hành sơn', 'sơn trà'],
            'note' => [
                'vi' => 'Đà Nẵng dễ tư vấn cho gia đình vì có biển, Bà Nà/Cầu Vàng và có thể kết hợp Hội An trong lịch trình ngắn.',
                'en' => 'Da Nang is easy to recommend for families because it combines beach time, Ba Na/Golden Bridge and optional Hoi An in a short trip.',
            ],
            'questions' => [
                'vi' => ['Khách muốn ở gần biển Mỹ Khê hay trung tâm sông Hàn?', 'Có muốn đi Bà Nà Hills/Cầu Vàng không?'],
                'en' => ['Do you prefer My Khe Beach or Han River city center?', 'Would you like Ba Na Hills/Golden Bridge included?'],
            ],
        ],
        'nha_trang' => [
            'keywords' => ['nha trang', 'cam ranh', 'vinwonders', 'tháp bà', 'ponagar', 'tắm bùn', 'tour đảo', 'bãi dài'],
            'note' => [
                'vi' => 'Nha Trang hợp khách thích biển, tour đảo, hải sản và các trải nghiệm nhẹ như VinWonders hoặc tắm bùn khoáng.',
                'en' => 'Nha Trang suits beach-focused guests, island tours, seafood and lighter experiences such as VinWonders or mud baths.',
            ],
            'questions' => [
                'vi' => ['Khách muốn nghỉ khu trung tâm Nha Trang hay resort Bãi Dài/Cam Ranh?', 'Có muốn thêm tour đảo hoặc VinWonders không?'],
                'en' => ['Do you prefer central Nha Trang or Bai Dai/Cam Ranh resorts?', 'Would you like island tours or VinWonders included?'],
            ],
        ],
        'phu_quoc' => [
            'keywords' => ['phú quốc', 'phu quoc', 'sunset town', 'hòn thơm', 'vinwonders', 'safari', 'nam đảo', 'bắc đảo'],
            'note' => [
                'vi' => 'Phú Quốc mạnh về nghỉ dưỡng biển, resort, sunset và các cụm vui chơi phù hợp gia đình.',
                'en' => 'Phu Quoc is strong for beach resorts, sunsets and family-friendly entertainment complexes.',
            ],
            'questions' => [
                'vi' => ['Khách muốn ở resort biển hay khách sạn trung tâm?', 'Lịch trình có cần Hòn Thơm, VinWonders hoặc Safari không?'],
                'en' => ['Do you prefer a beach resort or central hotel?', 'Should the itinerary include Hon Thom, VinWonders or Safari?'],
            ],
        ],
        'da_lat' => [
            'keywords' => ['đà lạt', 'da lat', 'dalat', 'langbiang', 'săn mây', 'fresh garden', 'puppy farm', 'hồ xuân hương'],
            'note' => [
                'vi' => 'Đà Lạt hợp khách muốn khí hậu mát, cafe/view đẹp, lịch trình nhẹ và nhiều điểm chụp hình.',
                'en' => 'Da Lat suits guests who want cool weather, scenic cafes, a lighter pace and photo-friendly stops.',
            ],
            'questions' => [
                'vi' => ['Khách muốn đi xe hay bay đến Đà Lạt?', 'Có muốn thêm săn mây hoặc các điểm cafe/view đẹp không?'],
                'en' => ['Will guests travel by road or by flight?', 'Would you like cloud hunting or scenic cafes included?'],
            ],
        ],
        'sa_pa' => [
            'keywords' => ['sa pa', 'sapa', 'fansipan', 'cát cát', 'ô quy hồ', 'thác bạc', 'cầu kính'],
            'note' => [
                'vi' => 'Sa Pa hợp khách thích cảnh núi, khí hậu mát và trải nghiệm bản làng, nhưng cần lưu ý thời tiết và sức khỏe khi di chuyển.',
                'en' => 'Sa Pa suits mountain scenery, cool weather and village experiences, but weather and guest mobility should be checked.',
            ],
            'questions' => [
                'vi' => ['Khách có muốn đi Fansipan không?', 'Trong đoàn có ai ngại đi bộ nhiều hoặc say xe đường núi không?'],
                'en' => ['Would guests like to visit Fansipan?', 'Does anyone avoid long walks or mountain roads?'],
            ],
        ],
        'japan' => [
            'keywords' => ['nhật bản', 'japan', 'tokyo', 'osaka', 'kyoto', 'núi phú sĩ', 'fuji'],
            'note' => [
                'vi' => 'Nhật Bản dễ tư vấn theo mùa hoa anh đào, lá đỏ, mua sắm và văn hóa; cần kiểm tra visa và ngày khởi hành sớm.',
                'en' => 'Japan is easy to advise by cherry blossom, autumn foliage, shopping and culture; visa and departure dates should be checked early.',
            ],
            'questions' => [
                'vi' => ['Khách muốn đi mùa nào: hoa anh đào, hè, lá đỏ hay mùa đông?', 'Khách đã có visa Nhật hoặc cần Travel Plus hỗ trợ hồ sơ chưa?'],
                'en' => ['Which season do guests prefer: cherry blossom, summer, autumn foliage or winter?', 'Do guests already have Japan visas or need support?'],
            ],
        ],
        'korea' => [
            'keywords' => ['hàn quốc', 'han quoc', 'korea', 'seoul', 'nami', 'everland', 'busan'],
            'note' => [
                'vi' => 'Hàn Quốc hợp khách đi lần đầu nhờ tuyến Seoul/Nami/mua sắm dễ đi, dễ kết hợp trải nghiệm văn hóa và ẩm thực.',
                'en' => 'South Korea suits first-time travelers with accessible Seoul/Nami/shopping routes plus culture and food experiences.',
            ],
            'questions' => [
                'vi' => ['Khách muốn tập trung Seoul hay kết hợp Nami/Busan?', 'Khách cần hỗ trợ visa Hàn Quốc không?'],
                'en' => ['Do guests prefer Seoul only or adding Nami/Busan?', 'Do guests need Korea visa support?'],
            ],
        ],
        'thailand' => [
            'keywords' => ['thái lan', 'thai lan', 'thailand', 'bangkok', 'pattaya', 'phuket', 'chiang mai'],
            'note' => [
                'vi' => 'Thái Lan phù hợp khách muốn tour nước ngoài dễ đi, ngân sách mềm, mua sắm và ẩm thực rõ ràng.',
                'en' => 'Thailand suits guests who want an easy outbound trip with approachable budget, shopping and food.',
            ],
            'questions' => [
                'vi' => ['Khách muốn Bangkok/Pattaya hay biển Phuket?', 'Ưu tiên mua sắm, vui chơi hay nghỉ dưỡng?'],
                'en' => ['Do guests prefer Bangkok/Pattaya or Phuket beach?', 'Is the priority shopping, entertainment or resort time?'],
            ],
        ],
        'singapore' => [
            'keywords' => ['singapore', 'sentosa', 'gardens by the bay', 'marina bay', 'jewel changi', 'universal'],
            'note' => [
                'vi' => 'Singapore hợp lịch trình ngắn, sạch sẽ, dễ đi cho gia đình và khách lần đầu đi nước ngoài.',
                'en' => 'Singapore works well for short, clean and easy trips for families and first-time outbound guests.',
            ],
            'questions' => [
                'vi' => ['Khách có muốn thêm Universal/Sentosa không?', 'Ngân sách đã gồm vé tham quan chưa?'],
                'en' => ['Would guests like Universal/Sentosa included?', 'Does the budget include attraction tickets?'],
            ],
        ],
        'europe' => [
            'keywords' => ['châu âu', 'chau au', 'europe', 'pháp', 'france', 'ý', 'italy', 'thụy sĩ', 'switzerland', 'đức', 'germany'],
            'note' => [
                'vi' => 'Châu Âu là tuyến giá trị cao, cần chuẩn bị sớm về visa Schengen, lịch bay, sức khỏe và số ngày đi.',
                'en' => 'Europe is a higher-value route that needs early Schengen visa, flight, health and trip-length planning.',
            ],
            'questions' => [
                'vi' => ['Khách đã từng có visa Schengen chưa?', 'Muốn đi một nước sâu hơn hay liên tuyến nhiều nước?'],
                'en' => ['Have guests held a Schengen visa before?', 'Do they prefer one country in depth or a multi-country route?'],
            ],
        ],
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $fallbacks = [
        'domestic' => [
            'strength' => [
                'vi' => 'Phù hợp khách muốn chuyến đi trong nước dễ triển khai, có Travel Plus hỗ trợ theo nhu cầu.',
                'en' => 'Suitable for guests who want a practical domestic trip with Travel Plus support.',
            ],
            'suitable_for' => [
                'vi' => ['nhóm nhỏ', 'gia đình', 'company trip'],
                'en' => ['small groups', 'families', 'company trips'],
            ],
        ],
        'outbound' => [
            'strength' => [
                'vi' => 'Phù hợp khách muốn tour nước ngoài có khung dịch vụ rõ ràng và được hỗ trợ trước chuyến đi.',
                'en' => 'Suitable for guests who want an organized outbound tour with clear service flow and pre-trip support.',
            ],
            'suitable_for' => [
                'vi' => ['khách đi nước ngoài', 'gia đình', 'nhóm bạn'],
                'en' => ['outbound travelers', 'families', 'friend groups'],
            ],
        ],
    ];

    /**
     * @var array<string, array<string, array<string, string>>>
     */
    public array $paces = [
        'light' => [
            'label' => ['vi' => 'nhẹ', 'en' => 'light'],
            'note' => [
                'vi' => 'Nhịp đi nhẹ, phù hợp khách muốn có thêm thời gian nghỉ.',
                'en' => 'Easy pace, suitable when guests prefer more rest time.',
            ],
        ],
        'balanced' => [
            'label' => ['vi' => 'vừa phải', 'en' => 'balanced'],
            'note' => [
                'vi' => 'Cân bằng giữa tham quan và độ thoải mái khi di chuyển.',
                'en' => 'Balanced between sightseeing and travel comfort.',
            ],
        ],
        'dense' => [
            'label' => ['vi' => 'khá dày', 'en' => 'full schedule'],
            'note' => [
                'vi' => 'Đi nhiều điểm, hợp khách muốn tham quan nhiều trong một chuyến.',
                'en' => 'Covers many stops, better for guests who want to see more in one trip.',
            ],
        ],
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $budgetSegments = [
        'domestic' => [
            ['max' => 5000000, 'label' => ['vi' => 'phổ thông trong nước', 'en' => 'economy domestic']],
            ['max' => 10000000, 'label' => ['vi' => 'tầm trung trong nước', 'en' => 'mid-range domestic']],
            ['max' => null, 'label' => ['vi' => 'cao hơn mặt bằng tour trong nước', 'en' => 'premium domestic']],
        ],
        'outbound' => [
            ['max' => 18000000, 'label' => ['vi' => 'tour nước ngoài phổ thông', 'en' => 'entry outbound']],
            ['max' => 40000000, 'label' => ['vi' => 'tour nước ngoài tầm trung', 'en' => 'mid-range outbound']],
            ['max' => null, 'label' => ['vi' => 'tour nước ngoài cao cấp', 'en' => 'premium outbound']],
        ],
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $serviceAddons = [
        'domestic_vi' => ['Vé máy bay', 'Khách sạn', 'Xe đưa đón', 'Bảo hiểm du lịch'],
        'domestic_en' => ['Flight tickets', 'Hotels', 'Transportation', 'Travel insurance'],
        'outbound_vi' => ['Tư vấn visa', 'Vé máy bay', 'Khách sạn', 'Xe đưa đón', 'Bảo hiểm du lịch'],
        'outbound_en' => ['Visa consultation', 'Flight tickets', 'Hotels', 'Transportation', 'Travel insurance'],
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $nextQuestions = [
        'domestic_vi' => [
            'Ngày đi dự kiến hoặc khoảng ngày linh hoạt',
            'Số lượng khách và nhóm khách đi cùng',
            'Ngân sách dự kiến mỗi khách',
            'Muốn lịch trình nghỉ dưỡng hay tham quan nhiều điểm',
        ],
        'domestic_en' => [
            'Expected travel date or flexible date range',
            'Number of guests and guest profile',
            'Approximate budget per guest',
            'Preferred style: resort time or more sightseeing',
        ],
        'outbound_vi' => [
            'Ngày đi dự kiến hoặc khoảng ngày linh hoạt',
            'Số lượng khách và nhóm khách đi cùng',
            'Ngân sách dự kiến mỗi khách',
            'Tình trạng hộ chiếu và nhu cầu hỗ trợ visa',
        ],
        'outbound_en' => [
            'Expected travel date or flexible date range',
            'Number of guests and guest profile',
            'Approximate budget per guest',
            'Passport validity and visa support needs',
        ],
    ];

    /**
     * @var array<string, array<string, string>>
     */
    public array $cautions = [
        'dense' => [
            'vi' => 'Nên hỏi thêm độ tuổi, sức khỏe và nhịp đi mong muốn trước khi chốt tư vấn.',
            'en' => 'Ask about age, health and preferred pace before recommending this as the final option.',
        ],
        'outbound' => [
            'vi' => 'Nên kiểm tra hạn hộ chiếu, nhu cầu visa và ngày khởi hành còn chỗ trước khi báo giá.',
            'en' => 'Check passport validity, visa needs and available departure dates before quoting.',
        ],
        'domestic' => [
            'vi' => 'Nên xác nhận ngày đi, cấu hình phòng và nhu cầu phương tiện trước khi báo giá.',
            'en' => 'Confirm departure date, room setup and transport needs before quoting.',
        ],
    ];
}
