import csv
import random
from faker import Faker
from datetime import datetime

fake = Faker()

# ==========================================
# 1. DATA REAL QUEMADA (Contexto Reddit/Gaming)
# ==========================================
REAL_GENRES = ["Action RPG", "First-Person Shooter", "Sandbox", "MOBA", "Metroidvania", "Battle Royale", "Simulation", "Strategy", "Horror", "Platformer"]
REAL_PLATFORMS = [
    ("PC", "Microsoft"), ("PlayStation 5", "Sony"), ("Nintendo Switch", "Nintendo"), 
    ("Xbox Series X", "Microsoft"), ("Steam Deck", "Valve"), ("iOS", "Apple"), 
    ("Android", "Google"), ("PlayStation 4", "Sony"), ("Mac", "Apple")
]
REAL_TAGS = ["Discussion", "Meme", "News", "Guide", "Spoiler", "Fanart", "Question", "LFG", "Review", "Esports", "Rumor"]
REAL_AWARDS = ["Gold", "Silver", "Platinum", "Helpful", "Wholesome", "Take My Energy", "Mind Blown", "F in the Chat", "Poggers"]
REAL_COMMUNITIES = [
    "r/gaming", "r/pcmasterrace", "r/patientgamers", "r/EldenRing", "r/Minecraft", 
    "r/Helldivers", "r/leagueoflegends", "r/GlobalOffensive", "r/truegaming", 
    "r/NintendoSwitch", "r/PS5", "r/indiegames", "r/FortniteBR", "r/Valorant", "r/skyrim"
]
REAL_GAMES = [
    ("Elden Ring", "FromSoftware"), ("Minecraft", "Mojang"), ("Helldivers 2", "Arrowhead"),
    ("Baldur's Gate 3", "Larian Studios"), ("Cyberpunk 2077", "CD Projekt Red"),
    ("Counter-Strike 2", "Valve"), ("League of Legends", "Riot Games"), 
    ("The Legend of Zelda: Tears of the Kingdom", "Nintendo"), ("Fortnite", "Epic Games"),
    ("Valorant", "Riot Games"), ("Red Dead Redemption 2", "Rockstar Games"),
    ("Stardew Valley", "ConcernedApe"), ("Hollow Knight", "Team Cherry"),
    ("Grand Theft Auto V", "Rockstar North"), ("Apex Legends", "Respawn"),
    ("Palworld", "Pocketpair"), ("The Witcher 3: Wild Hunt", "CD Projekt Red"),
    ("Hades", "Supergiant Games"), ("Super Mario Odyssey", "Nintendo"), ("Overwatch 2", "Blizzard")
]

# ==========================================
# 2. CONFIGURACIÓN DE CANTIDADES MASIVAS
# ==========================================
NUM_USERS = 3200
NUM_POSTS = 2000
# Total nodos fijos: ~85. Total nodos generados: 5200. Total general > 5285 nodos.

# Listas en memoria para relacionarlos después
users = []
posts = []

def format_list(items):
    return "|".join(items)

def random_boolean():
    return random.choice(['true', 'false'])

# ==========================================
# 3. GENERADORES DE NODOS
# ==========================================
print("Generando Nodos con contexto real...")

with open('nodes_Genre.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['name', 'slug', 'description', 'colorHex', 'popularityRank'])
    for name in REAL_GENRES:
        writer.writerow([name, name.replace(" ", "-").lower(), fake.sentence(), fake.hex_color(), random.randint(1, 10)])

with open('nodes_Platform.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['name', 'manufacturer', 'generation', 'releasedAt', 'websiteUrl', 'iconUrl'])
    for name, manufacturer in REAL_PLATFORMS:
        writer.writerow([name, manufacturer, random.randint(8, 9), fake.date_this_century().isoformat(), fake.url(), fake.image_url()])

with open('nodes_Tag.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['name', 'description', 'createdAt', 'usageCount', 'isOfficial'])
    for name in REAL_TAGS:
        writer.writerow([name, fake.sentence(), fake.date_between(start_date='-2y', end_date='today').isoformat(), random.randint(50, 10000), random_boolean()])

with open('nodes_Award.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['name', 'coinCost', 'isRare', 'iconUrl', 'createdAt'])
    for name in REAL_AWARDS:
        writer.writerow([name, random.randint(50, 5000), random_boolean(), fake.image_url(), fake.date_between(start_date='-5y', end_date='today').isoformat()])

with open('nodes_Game.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['title', 'developer', 'releaseDate', 'metacriticScore', 'features'])
    for title, developer in REAL_GAMES:
        features = format_list(["Multiplayer" if random_boolean() == 'true' else "Singleplayer", "Achievements", "Cloud Saves"])
        writer.writerow([title, developer, fake.date_between(start_date='-10y', end_date='today').isoformat(), round(random.uniform(70.0, 98.0), 1), features])

with open('nodes_Community.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['name', 'createdDate', 'memberCount', 'isNSFW', 'rules'])
    for name in REAL_COMMUNITIES:
        rules = format_list(["Be respectful", "No spam", "No spoilers in titles"])
        writer.writerow([name, fake.date_between(start_date='-8y', end_date='today').isoformat(), random.randint(10000, 5000000), random_boolean(), rules])

print("Generando miles de Posts y Usuarios (Esto toma unos segundos)...")

with open('nodes_Post.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['id', 'title', 'body', 'createdAt', 'upvotes', 'keywords'])
    for i in range(NUM_POSTS):
        post_id = f"post_{i}"
        posts.append(post_id)
        keywords = format_list([fake.word(), fake.word()])
        writer.writerow([post_id, fake.catch_phrase(), fake.text(max_nb_chars=200), fake.date_between(start_date='-1y', end_date='today').isoformat(), random.randint(-50, 25000), keywords])

with open('nodes_User.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['username', 'joinDate', 'karmaPoints', 'isPremium', 'socialLinks'])
    for _ in range(NUM_USERS):
        username = fake.unique.user_name()
        users.append(username)
        socials = format_list([fake.url()])
        writer.writerow([username, fake.date_between(start_date='-10y', end_date='today').isoformat(), random.randint(0, 150000), random_boolean(), socials])

# ==========================================
# 4. GENERADORES DE RELACIONES (Grafo Conexo)
# ==========================================
print("Generando Relaciones...")

# IS_OF (Game -> Genre)
with open('rel_IS_OF.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['game_title', 'genre_name', 'assignedAt', 'isPrimaryGenre', 'matchPercentage'])
    for title, _ in REAL_GAMES:
        writer.writerow([title, random.choice(REAL_GENRES), fake.date_this_year().isoformat(), random_boolean(), round(random.uniform(50.0, 100.0), 2)])

# RELEASED_ON (Game -> Platform)
with open('rel_RELEASED_ON.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['game_title', 'platform_name', 'releasedAt', 'isPorted', 'targetFps'])
    for title, _ in REAL_GAMES:
        writer.writerow([title, random.choice(REAL_PLATFORMS)[0], fake.date_this_year().isoformat(), random_boolean(), random.choice([30, 60, 120])])

# RELATED_TO (Community -> Game) - Conecta todas las comunidades al menos a un juego
with open('rel_RELATED_TO.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['community_name', 'game_title', 'establishedAt', 'officialLink', 'priorityLevel'])
    for comm in REAL_COMMUNITIES:
        writer.writerow([comm, random.choice(REAL_GAMES)[0], fake.date_this_year().isoformat(), fake.url(), random.randint(1, 5)])

# POSTED_IN (Post -> Community) - Conecta todos los posts a las comunidades
with open('rel_POSTED_IN.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['post_id', 'community_name', 'isPinned', 'allowsComments', 'offTopic'])
    for post in posts:
        writer.writerow([post, random.choice(REAL_COMMUNITIES), random_boolean(), 'true', random_boolean()])

# WROTE (User -> Post) - Garantiza que TODOS los usuarios hayan escrito al menos un post
with open('rel_WROTE.csv', 'w', newline='', encoding='utf-8') as f:
    writer = csv.writer(f)
    writer.writerow(['username', 'post_id', 'clientApp', 'isEdited', 'location'])
    for i, user in enumerate(users):
        post_to_assign = posts[i % len(posts)] # Recicla posts si hay más usuarios que posts
        writer.writerow([user, post_to_assign, random.choice(['iOS', 'Android', 'Web', 'Desktop']), random_boolean(), fake.country()])

# Relaciones adicionales aleatorias para enriquecer el grafo
def generate_random_relation(filename, header, list_a, list_b, num_rows, prop_generator):
    with open(filename, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(header)
        for _ in range(num_rows):
            item_a = random.choice(list_a)
            item_b = random.choice(list_b)
            # Manejo por si mandamos una tupla de REAL_GAMES o REAL_PLATFORMS
            val_a = item_a[0] if isinstance(item_a, tuple) else item_a
            val_b = item_b[0] if isinstance(item_b, tuple) else item_b
            writer.writerow([val_a, val_b] + prop_generator())

generate_random_relation('rel_FOLLOWS.csv', ['username_a', 'username_b', 'sinceAt', 'notificationsOn', 'closeFriend'], users, users, 2500, 
                         lambda: [fake.date_this_year().isoformat(), random_boolean(), random_boolean()])

generate_random_relation('rel_MEMBER_OF.csv', ['username', 'community_name', 'joinedAt', 'role', 'isActive'], users, REAL_COMMUNITIES, 3500, 
                         lambda: [fake.date_this_year().isoformat(), random.choice(['Member', 'Moderator']), random_boolean()])

generate_random_relation('rel_UPVOTED.csv', ['username', 'post_id', 'upvotedAt', 'voteWeight', 'isSuperVote'], users, posts, 5000, 
                         lambda: [fake.date_this_month().isoformat(), random.randint(1, 3), random_boolean()])

generate_random_relation('rel_COMMENTED_ON.csv', ['username', 'post_id', 'comment', 'commentedAt', 'isReply'], users, posts, 4000, 
                         lambda: [fake.sentence(), fake.date_this_month().isoformat(), random_boolean()])

generate_random_relation('rel_LIKES.csv', ['username', 'genre_name', 'likedAt', 'intensityLevel', 'isPublic'], users, REAL_GENRES, 1500, 
                         lambda: [fake.date_this_year().isoformat(), random.randint(1, 10), random_boolean()])

generate_random_relation('rel_CROSSPOSTED_TO.csv', ['post_id', 'community_name', 'crosspostedAt', 'karmaGained', 'reason'], posts, REAL_COMMUNITIES, 500, 
                         lambda: [fake.date_this_month().isoformat(), random.randint(10, 500), fake.sentence()])

generate_random_relation('rel_RECEIVED_AWARD.csv', ['post_id', 'award_name', 'grantedAt', 'quantity', 'givenByUser'], posts, REAL_AWARDS, 1000, 
                         lambda: [fake.date_this_month().isoformat(), random.randint(1, 5), random_boolean()])

generate_random_relation('rel_TAGGED_WITH.csv', ['post_id', 'tag_name', 'addedAt', 'confidenceScore', 'isModeratorApplied'], posts, REAL_TAGS, 2000, 
                         lambda: [fake.date_this_month().isoformat(), round(random.uniform(0.1, 1.0), 2), random_boolean()])

generate_random_relation('rel_ABOUT.csv', ['post_id', 'game_title', 'containsSpoilers', 'isReview', 'firstTime'], posts, REAL_GAMES, 1500, 
                         lambda: [random_boolean(), random_boolean(), random_boolean()])

print("¡Listo! Se generaron todos los archivos CSV con contexto de gaming real.")