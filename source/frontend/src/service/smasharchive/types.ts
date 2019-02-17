export type Pagination = {
  first: number;
  last: number;
  current: number;
  previous: number;
  next: number;
  numItemsPerPage: number;
  pageCount: number;
  totalCount: number;
  pageRange: number;
  startPage: number;
  endPage: number;
  pagesInRange: number[];
  firstPageInRange: number;
  lastPageInRange: number;
  currentItemCount: number;
  firstItemNumber: number;
  lastItemNumber: number;
};
