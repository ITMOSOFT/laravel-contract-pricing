# Release: v1.0.0

## Summary

Initial stable release of ITMOSOFT Laravel Contract Pricing.

## Highlights

- Polymorphic customer-specific contract price lists.
- Product-specific negotiated prices.
- Quantity-based price breaks.
- Currency support.
- Active/inactive and effective date controls.
- Priority-based price list resolution.
- Resolver returns a typed result object.
- Configurable model and table names.

## Compatibility

- PHP 8.3+
- Laravel 12 and 13

## Validation

Run the full test suite and CI matrix before tagging this release:

```bash
composer install
composer test
```

Do not publish this release until local tests and GitHub Actions pass.
