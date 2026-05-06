import re

with open('database/seeders/DatabaseSeeder.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Multiply delivery_fee by 10 (e.g. 2.50 -> 25.00)
content = re.sub(r"'delivery_fee'\s*=>\s*([\d\.]+)", lambda m: f"'delivery_fee' => {float(m.group(1)) * 10:.2f}", content)

# Multiply base_price by 40 (e.g. 10.99 -> 439.60, round to int or keep 2 decimals)
content = re.sub(r"'base_price'\s*=>\s*([\d\.]+)", lambda m: f"'base_price' => {round(float(m.group(1)) * 40)}", content)

# Multiply price_modifier by 40
content = re.sub(r"'price_modifier'\s*=>\s*([\d\.]+)", lambda m: f"'price_modifier' => {round(float(m.group(1)) * 40)}", content)

# Make tips more realistic (0 to 50 EGP instead of 0 to 3)
content = content.replace("rand(0, 3)", "rand(0, 50)")

with open('database/seeders/DatabaseSeeder.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("DatabaseSeeder.php updated with realistic EGP prices.")
