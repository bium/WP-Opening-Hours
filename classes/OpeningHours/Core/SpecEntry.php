<?php

namespace OpeningHours\Core;

/**
 * Describes a specification entry in the Opening Hours data tree
 * @package OpeningHours\Core
 */
interface SpecEntry extends ArraySerializable {
  /**
   * Returns the kind of specification entry
   * @return    string
   */
  function getKind(): string;

  /**
   * Returns the children of the specification entry
   * @return    SpecEntry[]
   */
  function getChildren(): array;

  /**
   * Returns a ValidityPeriod for a specification entry
   * @return    ValidityPeriod
   */
  function getValidityPeriod(): ValidityPeriod;

  /**
   * Transforms a ValidityPeriod that is about to cover this SpecEntry's ValidityPeriod
   * if necessary (e.g. postponing a SpecEntry until a better suited moment).
   * Will be invoked when this item is active when `$coveringPeriod` starts.
   *
   * If a SpecEntry implementation does not require custom transformation, the incoming
   * $period can be passed through.
   *
   * @param     ValidityPeriod    $coveringPeriod   Incoming ValidityPeriod covering this period
   * @return    ValidityPeriod                      Transformed Period
   */
  function transformCoveringPeriod(ValidityPeriod $coveringPeriod): ValidityPeriod;
}
