# Calcdown Examples

This file contains practical examples of Calcdown usage.

## Basic Arithmetic

```calcdown
# Simple calculations
2 + 2
5 * (3 + 1)
10 / 2
2 ^ 3

# With order of operations
5 + 3 * 2       # Result: 11 (multiplication first)
(5 + 3) * 2     # Result: 16 (parentheses first)
```

## Currency Calculations

```calcdown
# Basic currency operations
$100 + $50      # Result: $150 (USD)
€200 - 10%      # Result: 180 (EUR)

# Currency conversions
£50 in Euros    # Result: 57.5 (EUR)
$100 in EUR     # Result: 92 (EUR)

# Price calculations
item = $29.99 times 3
tax = item + 8.5%
total = tax     # Result: $97.67 (USD)
```

## Percentage Calculations

```calcdown
# Add percentage
100 + 20%       # Result: 120

# Subtract percentage
200 - 15%       # Result: 170

# Reverse percentage (finding the whole)
20% of what is 50   # Result: 250
```

## Unit Conversions

```calcdown
# Length
100 cm in m         # Result: 1 (m)
50 cm in inches     # Result: 50 (inches)

# Volume
20 ml in teaspoons  # Result: 4.05 (tsp)
```

## Date Arithmetic

```calcdown
# Add days to today
today + 7 days      # Result: date 7 days from now
today + 30 days     # Result: date 30 days from now

# Project planning
start = today
milestone1 = start + 14 days
milestone2 = milestone1 + 21 days
deadline = milestone2 + 7 days
```

## Variables and Multi-line Calculations

```calcdown
# Simple variables
x = 10
y = 5
result = x + y      # Result: 15

# Reusing calculated values
price = $100 times 1
discount = price - 25%
tax = discount + 8.5%
final = tax         # Result: $81.38 (USD)
```

## Real-World Use Cases

### Invoice Calculator

```calcdown
# Item costs
item1 = $125 times 1
item2 = $89.50 times 1
item3 = $45 times 2

# Subtotal
subtotal = item1 + item2 + item3

# Apply discount
discounted = subtotal - 10%

# Add tax
tax_rate = 8.5%
final_total = discounted + tax_rate
```

### Hourly Rate Calculator

```calcdown
# Calculate weekly earnings
hourly = $45 times 1
hours_per_day = 8
days_per_week = 5

# Calculations
daily_rate = hourly * hours_per_day
weekly_rate = daily_rate * days_per_week
monthly_rate = weekly_rate * 4
yearly_rate = monthly_rate * 12
```

### Recipe Converter

```calcdown
# Original recipe (serves 4)
flour = 250 ml
sugar = 200 ml
butter = 100 ml

# Convert to teaspoons
flour_tsp = flour in teaspoons
sugar_tsp = sugar in teaspoons
butter_tsp = butter in teaspoons

# Scale for 6 servings
scaling_factor = 1.5
flour_scaled = flour * scaling_factor
sugar_scaled = sugar * scaling_factor
butter_scaled = butter * scaling_factor
```

### Construction Material Calculator

```calcdown
# Wall dimensions
wall_length = 25 cm * 6     # 6 sections of 25cm
wall_height = 30 cm * 4     # 4 rows of 30cm

# Add 5% buffer for waste
length_buffered = wall_length + 5%
height_buffered = wall_height + 5%

# Convert to meters
length_m = length_buffered in m
height_m = height_buffered in m
```

### Freelance Quote Calculator

```calcdown
# Project parameters
hourly_rate = $85 times 1
estimated_hours = 40

# Base cost
base_cost = hourly_rate * estimated_hours

# Add contingency
with_contingency = base_cost + 15%

# Convert to client's currency
quote_in_euros = with_contingency in EUR
```

### Savings Goal Calculator

```calcdown
# Income and expenses
monthly_income = $6000 times 1
expenses = monthly_income - 35%

# Savings targets
emergency_fund = monthly_income * 20%
retirement = monthly_income * 15%
vacation = monthly_income * 10%

# Total savings
total_savings = emergency_fund + retirement + vacation

# Remaining for discretionary
discretionary = expenses - total_savings
```

## Comments

```calcdown
# Full line comments are ignored
2 + 2 # Inline comments work too

# You can document your calculations
price = $100 times 1  # Base price before discounts
discount = price - 20%    # Apply 20% off
final = discount + 8.5%   # Add sales tax
```

## Tips

1. **Use `times` for currency**: When assigning currency values, use `times 1` to ensure proper evaluation
   ```calcdown
   price = $50 times 1   # ✓ Works
   ```

2. **Chain conversions**: You can apply multiple operations in sequence
   ```calcdown
   price = $100 times 1
   discounted = price - 25%
   in_euros = discounted in EUR
   ```

3. **Percentages are operations**: When you use `+ 20%` or `- 10%`, it operates on the left value
   ```calcdown
   100 + 20%   # Adds 20% of 100 = 120
   200 - 15%   # Subtracts 15% of 200 = 170
   ```

4. **Variables preserve context**: Variables in blocks maintain their values for subsequent lines
   ```calcdown
   a = 10
   b = a + 5    # b = 15
   c = a + b    # c = 25
   ```
