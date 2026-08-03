ALTER TABLE expense_entries ADD INDEX idx_expenses_scope_date (company_id, entry_date, farm_id, block_id, status);
ALTER TABLE labor_entries ADD INDEX idx_labor_scope_date (company_id, labor_date, farm_id, block_id, status);
ALTER TABLE production_entries ADD INDEX idx_production_scope_date (company_id, production_date, farm_id, block_id);
ALTER TABLE budgets ADD INDEX idx_budgets_period_scope (company_id, period_start, period_end, season_id, cost_center_id);
