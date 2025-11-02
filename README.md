<div align="center">
  <img src="docs/calcdown.png" alt="Calcdown Logo" width="200">
  
  # Calcdown
  
  **A human-readable calculator DSL for web applications**
  
  [![Tests](https://img.shields.io/badge/tests-passing-brightgreen)](tests/)
  [![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](tests/)
  [![Type Coverage](https://img.shields.io/badge/types-100%25-brightgreen)](phpstan.neon.dist)
  [![PHP](https://img.shields.io/badge/php-%5E8.2-blue)](composer.json)
  [![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE.md)
</div>

---

## Why Calcdown?

Ever wished you could add **[Numi](https://numi.app)**-style calculations to your web application? Calcdown brings that magic to PHP.

Instead of building complex calculator UIs with buttons and operators, let your users write natural calculations like they would in a notebook:

```
price = $8 times 3
tax = price + 15%
total = tax in EUR
```

**Perfect for:**
- 📊 Financial dashboards and invoice calculators
- 🧮 Educational platforms teaching math or finance  
- 📝 Note-taking apps with computational capabilities
- 💰 Pricing calculators with unit conversions
- 📅 Date arithmetic for project planning

## Features

✨ **Human-Readable Syntax** - Write calculations the way you think  
🔢 **Unit Conversions** - Currency, measurements, and more  
📆 **Date Arithmetic** - Add days, calculate deadlines  
💱 **Currency Support** - USD, EUR, GBP with conversions  
🎯 **Variable Assignment** - Store and reuse values  
📐 **Order of Operations** - Proper PEMDAS/BODMAS handling  
💯 **100% Test Coverage** - Rock-solid and production-ready

## Installation

```bash
composer require ralphschindler/calcdown
```

## Quick Start

```php
use Calcdown\CalcdownParser;

$parser = new CalcdownParser();

// Simple calculation
$result = $parser->parseLine('2 + 2');
echo $result->result; // 4

// With units and conversions
$result = $parser->parseLine('$50 + 20%');
echo $result->result; // "60"
echo $result->resultUnits; // "USD"

// Multi-line calculations with variables
$block = <<<CALC
rate = $45 times 1
hours = 8
daily = rate * hours
weekly = daily * 5
CALC;

$results = $parser->parseBlock($block);
$weeklyRate = $results->finalLine();
echo $weeklyRate->result; // "1800"
```

## Expression Examples

### Basic Arithmetic

```php
2 + 2                    // 4
5 * (3 + 1)             // 20
10 / 2                  // 5
2 ^ 3                   // 8 (exponentiation)
10 % 3                  // 1 (modulo)
```

### Currency Calculations

```php
$100 + $50              // "150" (USD)
€200 - 10%              // 180 (EUR)
£50 in Euros            // 57.5 (EUR)
$7 * 4                  // "28" (USD)
```

### Unit Conversions

```php
100 cm in m             // 1 (m)
20 ml in teaspoons      // "4.05" (tsp)
50 cm in inches         // 50 (inches)
```

### Date Arithmetic

```php
today + 7 days          // "2025-11-09" (date)
today + 30 days         // "2025-12-02" (date)
```

### Variables & Assignment

```php
price = $8 times 3      // Assign $24 to price
tax = price * 8.5%      // Use price variable
total = price + tax     // "25.02" (USD approx)
```

### Percentage Calculations

```php
100 + 20%               // 120
200 - 15%               // 170
20% of what is 50       // 250 (reverse percentage)
```

### Complex Expressions

```php
// Combining multiple features
base = $299 times 1
discount = base - 25%
tax = discount + 8.5%
final = tax in EUR      // Approx "207.06" (EUR)

// Construction calculations
length = 25 cm * 6
buffered = length + 5%
meters = buffered in m    // 1.58 (m)
```

### Comments

```php
2 + 2 # This is a comment
# Lines starting with # are ignored
price = $100 # inline comments work too
```

## API Reference

### CalcdownParser

#### `parseLine(string $line, array $variables = []): LineEvaluation`

Parse and evaluate a single line expression.

**Parameters:**
- `$line` - The expression to parse
- `$variables` - Optional associative array of predefined variables

**Returns:** `LineEvaluation` object with:
- `expression` - Original expression string
- `result` - Calculated result (int|float|string)
- `resultUnits` - Unit of the result (string|null)
- `assignedVariables` - Variables defined in this line (array)

```php
$result = $parser->parseLine('price = $50 + 20%');
$result->result;              // "60"
$result->resultUnits;         // "USD"
$result->assignedVariables;   // ['price' => "60"]
```

#### `parseBlock(string $block): BlockEvaluation`

Parse and evaluate multiple lines, maintaining variable context.

**Parameters:**
- `$block` - Multi-line string of expressions

**Returns:** `BlockEvaluation` object with:
- `lines` - Array of `LineEvaluation` objects
- `finalLine()` - Get the last evaluated line (or null if empty)
- `toArray()` - Convert all results to array format

```php
$block = "a = 10\nb = 5\nresult = a + b";
$results = $parser->parseBlock($block);
$final = $results->finalLine();
$final->result; // 15
```

### LineEvaluation

```php
$line->toArray(); // Returns associative array:
// [
//     'expression' => '2 + 2',
//     'result' => 4,
//     'units' => null,
//     'assigned_variables' => []  // Only if variables were assigned
// ]
```

### BlockEvaluation

```php
$block->toArray(); // Returns array of line results
$block->finalLine(); // Returns last LineEvaluation or null
$block->lines; // Array of all LineEvaluation objects
```

## Supported Operators

| Operator | Description | Example |
|----------|-------------|---------|
| `+` | Addition | `5 + 3` → `8` |
| `-` | Subtraction | `10 - 4` → `6` |
| `*` or `times` or `x` | Multiplication | `4 * 3` → `12` |
| `/` | Division | `10 / 2` → `5` |
| `%` | Modulo | `10 % 3` → `1` |
| `^` | Exponentiation | `2 ^ 3` → `8` |
| `=` | Assignment | `x = 5` |
| `in` | Unit conversion | `100 cm in m` → `1` |
| `of what is` | Reverse percentage | `20% of what is 50` → `250` |

## Supported Units

### Currency
- `$` or `USD` - US Dollars
- `€` or `EUR` - Euros
- `£` or `GBP` - British Pounds

### Length
- `cm` - Centimeters
- `m` - Meters

### Volume
- `ml` - Milliliters
- `teaspoons` / `tsp` - Teaspoons

### Time
- `days` - Days (for date arithmetic)
- `date` - Date type (from `today` keyword)

### Percentages
- `%` - Percentage (e.g., `20%`)

## Special Identifiers

- `today` - Current date (can be used with `+ X days`)

## Use Cases

### 1. Invoice Calculator

```php
$invoice = <<<CALC
subtotal = $1250 times 1
discount = subtotal - 10%
tax = discount + 8.5%
total = tax
CALC;

$result = $parser->parseBlock($invoice);
```

### 2. Project Timeline Calculator

```php
$timeline = <<<CALC
project_start = today
design_phase = project_start + 14 days
development_phase = design_phase + 30 days
testing_phase = development_phase + 10 days
launch_date = testing_phase + 7 days
CALC;

$result = $parser->parseBlock($timeline);
$launch = $result->finalLine();
```

### 3. Unit Conversion Tool

```php
$conversions = <<<CALC
recipe_ml = 250 ml
in_teaspoons = recipe_ml in teaspoons

width_cm = 150 cm  
width_m = width_cm in m
CALC;

$result = $parser->parseBlock($conversions);
```

### 4. Financial Calculator

```php
$finance = <<<CALC
income = $5000 times 1
expenses = income - 30%
savings = income * 20%
remaining = expenses - savings
euros = remaining in EUR
CALC;

$result = $parser->parseBlock($finance);
```

## Error Handling

Currently, Calcdown focuses on parsing valid expressions. Invalid tokens or unrecognized operators are handled gracefully:

```php
// Invalid token returns error token
$parser->tokenize('2 & 3');  
// Returns error token with message

// Undefined variables default to 0
$result = $parser->parseLine('undefined_var + 5');
$result->result; // 5
```

## Development

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
XDEBUG_MODE=coverage vendor/bin/pest --coverage

# Run type checking
composer test:types
```

### Code Quality

- ✅ 100% Test Coverage
- ✅ 100% Type Coverage with PHPStan
- ✅ PSR-12 Coding Standards

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Roadmap

- [ ] More currency conversion rates (dynamic/configurable)
- [ ] Additional unit types (temperature, weight, speed)
- [ ] Function support (`sqrt()`, `round()`, etc.)
- [ ] Better error messages and validation
- [ ] Expression compilation for repeated use
- [ ] Plugin system for custom operators/units

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Inspiration

Calcdown is inspired by [Numi](https://numi.app), a beautiful calculator app for macOS. While Numi is a desktop application, Calcdown brings similar capabilities to web applications written in PHP.

## Acknowledgments

- Built with ❤️ using PHP 8.2+
- Tested with [Pest](https://pestphp.com)
- Type-safe with [PHPStan](https://phpstan.org)

---

<div align="center">
  Made with ☕ and 🧮
  
  [Report Bug](issues) · [Request Feature](issues)
</div>
