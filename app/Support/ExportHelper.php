<?php

declare(strict_types=1);

namespace App\Support;


use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Hyperf\Utils\Str;

trait ExportHelper
{
    public function export()
    {
        if (!$this instanceof ExportHelperInterface) {
            return $this->responseMsg('数据导出方法未完成');
        }

        $writer = WriterFactory::createFromType(Type::XLSX);
        // Waring 不可输出到浏览器
        // todo 定时清理文件
        $file_path = BASE_PATH . '/public/excel/' . Str::random(40) . '.' . Type::XLSX;
        $writer->openToFile($file_path);
        $writer->addRow(WriterEntityFactory::createRowFromArray($this->getExportHeaders()));

        foreach ($this->getExportData() as $row) {
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        }

        $writer->close();

        return $this->response->download($file_path);
    }
}
