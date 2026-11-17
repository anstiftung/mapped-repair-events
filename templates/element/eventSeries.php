<?php
declare(strict_types=1);

use Cake\I18n\Date;

/**
 * @var array<string, mixed> $seriesSettings
 * @var bool $isEditMode
 * @var \App\Model\Entity\Event $event
 * @var list<string>|null $seriesPreview
 * @var string|null $seriesError
 * @var bool $hasMultipleDates
 */
if ($isEditMode) {
    return;
}
$this->Form->unlockField('series');
$weekdays = $this->Time->getWeekdays();
$seed = $event->datumstart ?? Date::now();
$defaultWeekday = 'SA';
$settings = $seriesSettings + [
    'enabled' => ($seriesSettings['mode'] ?? '') === 'series', 'frequency' => 'monthly',
    'interval' => 1, 'positions' => ['1'], 'weekday' => $defaultWeekday,
    'start' => $seed->format('Y-m-d'), 'end' => Date::now()->addYears(1)->format('Y-m-d'),
    'excluded' => [],
];
$selectedWeekdays = $settings['weekdays'] ?? [$settings['weekday']];
$weekdayError = $seriesError === 'Bitte wähle mindestens einen gültigen Wochentag.' ? $seriesError : null;
$monthlyRules = $settings['monthly_rules'] ?? array_map(
    static fn(mixed $position): array => ['position' => $position, 'weekday' => $settings['weekday']],
    is_array($settings['positions']) ? $settings['positions'] : [],
);
if (!is_array($monthlyRules) || $monthlyRules === []) {
    $monthlyRules = [['position' => '1', 'weekday' => $defaultWeekday]];
}
$monthlyRule = array_values($monthlyRules)[0];
$monthlyRule = is_array($monthlyRule) ? $monthlyRule : [];
$rhythms = [
    'Monatlich' => [
        'monthly-1' => 'Jeden Monat',
        'monthly-2' => 'Jeden 2. Monat',
        'monthly-3' => 'Jeden 3. Monat (quartalsweise)',
    ],
    'Wöchentlich' => [
        'weekly-1' => 'Jede Woche',
        'weekly-2' => 'Alle 2 Wochen',
    ],
];
$rhythm = $settings['rhythm'] ?? $settings['frequency'] . '-' . $settings['interval'];
$seriesEnd = (string)$settings['end'];
$seriesEndDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $seriesEnd);
if ($seriesEndDate !== false && $seriesEndDate->format('Y-m-d') === $seriesEnd) {
    $seriesEnd = $seriesEndDate->format('d.m.Y');
}
?>
<section class="event-series" data-configured="<?= $seriesSettings !== [] ? '1' : '0' ?>">
        <?= $this->Form->control('series.enabled', [
            'type' => 'checkbox',
            'label' => [
                'text' => 'Diesen Termin wiederkehrend anlegen<span class="series-enabled-restriction"' . ($hasMultipleDates ? '' : ' hidden') . '> (nur mit einem einzigen Ausgangstermin möglich)</span>',
                'escape' => false,
            ],
            'checked' => !$hasMultipleDates && !empty($settings['enabled']),
            'disabled' => $hasMultipleDates,
        ]) ?>
    <fieldset class="series-rule-fields" hidden>
        <?= $this->Form->control('series.rhythm', [
            'type' => 'select', 'data-minimum-results-for-search' => -1, 'label' => 'Wiederholung', 'value' => $rhythm,
            'options' => $rhythms,
        ]) ?>
        <div class="series-weekly-days" hidden>
            <div class="input select">
                <label id="series-weekdays-label">Wochentage</label>
                <div class="series-weekday-options" role="group" aria-labelledby="series-weekdays-label" aria-describedby="series-weekdays-error" aria-invalid="<?= $weekdayError !== null ? 'true' : 'false' ?>">
                    <?= $this->Form->control('series.weekdays', [
                        'type' => 'select', 'multiple' => 'checkbox', 'label' => false,
                        'options' => $weekdays, 'value' => $selectedWeekdays,
                        'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                    ]) ?>
                </div>
                <div id="series-weekdays-error" class="error-message"<?= $weekdayError === null ? ' hidden' : '' ?>><?= h($weekdayError ?? '') ?></div>
            </div>
        </div>
        <div class="series-monthly-rules" hidden>
            <div class="series-monthly-rule-list">
                    <div class="series-monthly-rule">
                        <?= $this->Form->control('series.monthly_rules.0.position', [
                            'type' => 'select', 'data-minimum-results-for-search' => -1, 'label' => 'Position im Monat',
                            'options' => [1 => '1.', 2 => '2.', 3 => '3.', 4 => '4.', -1 => 'Letzter'],
                            'value' => $monthlyRule['position'] ?? '1',
                        ]) ?>
                        <?= $this->Form->control('series.monthly_rules.0.weekday', [
                            'type' => 'select', 'data-minimum-results-for-search' => -1, 'label' => 'Wochentag', 'options' => $weekdays,
                            'value' => $monthlyRule['weekday'] ?? $defaultWeekday,
                        ]) ?>
                    </div>
            </div>
        </div>
        <?= $this->Form->control('series.end', ['type' => 'text', 'class' => 'datepicker-input', 'label' => 'Wiederholen bis', 'value' => $seriesEnd, 'required' => true]) ?>
    </fieldset>
    <div class="series-preview-area" hidden>
        <button type="submit" name="series[preview]" value="1" class="button series-preview-button" formnovalidate>
            <i class="fa fa-refresh" aria-hidden="true"></i> Terminvorschau aktualisieren
        </button>
        <div id="event-series-preview" aria-live="polite" aria-busy="false">
            <?php if ($seriesError && $weekdayError === null): ?><p class="error-message"><?= h($seriesError) ?></p><?php endif; ?>
            <?php if ($seriesPreview !== null): ?>
                <?php
                $today = Date::now()->format('Y-m-d');
                $selectedPreviewDates = array_filter($seriesPreview, static fn(string $date): bool => $date >= $today && !in_array($date, $settings['excluded'], true));
                ?>
                <h3>Terminvorschau</h3>
                <p class="series-preview-description">Diese Terminserie enthält folgende <span class="series-preview-count"><?= count($selectedPreviewDates) ?></span> Termine. Du kannst vor dem Speichern einzelne Termine wegklicken.</p>
                <div class="series-preview-list">
                    <?php foreach ($seriesPreview as $date): ?>
                        <label class="series-preview-row">
                            <input type="checkbox" class="series-include-date" data-date="<?= h($date) ?>" <?= in_array($date, $selectedPreviewDates, true) ? 'checked' : '' ?>>
                            <?= h((new Date($date))->i18nFormat('EEEE, dd.MM.yyyy')) ?>
                            <span class="series-preview-time"></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="series-excluded-dates">
        <?php foreach ($settings['excluded'] as $date): ?>
            <?= $this->Form->hidden('series.excluded[]', ['value' => $date]) ?>
        <?php endforeach; ?>
    </div>
</section>